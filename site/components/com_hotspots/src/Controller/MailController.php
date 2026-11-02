<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\CaptchaHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\RouteHelper;
use TLWebdesign\Component\Hotspots\Site\Model\HotspotModel;

\defined('_JEXEC') or die;

/**
 * Mails from the site: "mail this map" (mail.send, JSON) and "contact the author" (mail.contact).
 *
 * Both send from the site's own address with the visitor as Reply-To (never as From), and a session can
 * send at most MAX_PER_HOUR mails per hour.
 */
class MailController extends BaseController
{
    private const MAX_PER_HOUR = 5;

    private const SESSION_KEY = 'com_hotspots.mail.sent';

    /**
     * Mail a link to the map. Input: "model", a JSON object with mailto, subject, message, url, sender,
     * sender-email and the form token as key. Answers the same object with sent and status.
     */
    public function send(): void
    {
        $model = json_decode((string) $this->input->get('model', '', 'raw'));

        if (!\is_object($model)) {
            $this->json(['sent' => false, 'status' => "Couldn't send mail."]);
        }

        $model->sent = false;
        $token       = Session::getFormToken();

        if (!isset($model->$token)) {
            $model->status = "Couldn't send mail. Invalid form token.";
            $this->json($model);
        }

        unset($model->$token);

        if (!SettingsHelper::get('mail_map', 0)) {
            $model->status = "Couldn't send mail.";
            $this->json($model);
        }

        if (!$this->withinLimit()) {
            $model->status = "Couldn't send mail. Please try again later.";
            $this->json($model);
        }

        $user   = $this->app->getIdentity();
        $filter = InputFilter::getInstance();

        if ($user && !$user->guest) {
            $from   = (string) $user->email;
            $sender = (string) $user->name;
        } else {
            $from   = trim((string) $filter->clean($model->{'sender-email'} ?? '', 'string'));
            $sender = trim((string) $filter->clean($model->sender ?? '', 'string'));
        }

        $mailto  = trim((string) ($model->mailto ?? ''));
        $subject = (string) $filter->clean($model->subject ?? '', 'string');
        $body    = (string) $filter->clean($model->message ?? '', 'string');
        $url     = trim((string) $filter->clean($model->url ?? '', 'string'));

        // Only links to this site.
        if ($url === '' || !Uri::isInternal($url)) {
            $url = Uri::root();
        }

        if (!MailHelper::isEmailAddress($mailto) || !MailHelper::isEmailAddress($from)) {
            $model->status = 'Wrong email address provided.';
            $this->json($model);
        }

        $esc     = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $message = '<p>' . Text::sprintf('COM_HOTSPOTS_MAP_EXCERPT_SENT', $esc($from), $esc($sender), $esc($url)) . '</p>'
            . '<p>' . Text::_('COM_HOTSPOTS_MESSAGE') . ':</p>'
            . '<p>' . nl2br($esc($body)) . '</p>';

        $this->countMail();

        $model->sent   = $this->mail($mailto, $subject, $message, $from, $sender);
        $model->status = $model->sent ? 'Sent successfully' : "Couldn't send mail.";

        $this->json($model);
    }

    /**
     * Mail the author of a published marker (form on the marker page).
     */
    public function contact(): void
    {
        $this->checkToken();

        $user = $this->app->getIdentity();
        $id   = $this->input->getInt('hotspot_id');

        // The settings are checked here too: the form is only hidden in the template.
        if (!SettingsHelper::get('show_contact_author', 0)
            || !SecurityHelper::groupHasAccess($user->getAuthorisedGroups(), (array) SettingsHelper::get('authorized_contact_author_groups', []))
        ) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var HotspotModel $model */
        $model   = $this->getModel('Hotspot', 'Site', ['ignore_request' => true]);
        $hotspot = $id ? $model->getItem($id) : null;

        if (!$hotspot || HotspotModel::isHidden($hotspot) || empty($hotspot->created_by)) {
            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        $back = Route::_(RouteHelper::getHotspotRoute($hotspot->id . ':' . $hotspot->alias, (int) $hotspot->catid), false);

        $captcha = CaptchaHelper::plugin();

        if ($captcha !== '' && !CaptchaHelper::check($captcha, $this->input->post->getString('hs_captcha'))) {
            $this->setRedirect($back, Text::_('COM_HOTSPOTS_INVALID_CAPTCHA'), 'error');

            return;
        }

        if (!$this->withinLimit()) {
            $this->setRedirect($back, Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_MAIL_LIMIT'), 'error');

            return;
        }

        $filter  = InputFilter::getInstance();
        $name    = trim((string) $filter->clean($this->input->post->getString('name'), 'string'));
        $from    = trim((string) $this->input->post->getString('email'));
        $message = trim((string) $filter->clean($this->input->post->getString('message'), 'string'));

        if (!$user->guest) {
            $name = (string) $user->name;
            $from = (string) $user->email;
        }

        if (!MailHelper::isEmailAddress($from)) {
            $this->setRedirect($back, Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_WRONG_MAIL_ADDRESS'), 'warning');

            return;
        }

        if ($name === '' || $message === '') {
            $this->setRedirect($back, Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_INVALID_NAME'), 'warning');

            return;
        }

        $author = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById((int) $hotspot->created_by);

        if (!$author->id || !MailHelper::isEmailAddress((string) $author->email)) {
            $this->setRedirect($back, Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_MAIL_SENT_UNSUCCESSFUL'), 'error');

            return;
        }

        $subject = Text::sprintf('COM_HOTSPOTS_CONTACT_AUTHOR_EMAIL_SUBJECT', '"' . $hotspot->title . '"', $name);
        $body    = LayoutHelper::render('mail.contact', [
            'hotspot' => $hotspot,
            'author'  => $author,
            'name'    => $name,
            'from'    => $from,
            'message' => $message,
        ], JPATH_SITE . '/components/com_hotspots/layouts');

        $this->countMail();

        $sent = $this->mail((string) $author->email, $subject, $body, $from, $name);

        $this->setRedirect($back, Text::_($sent ? 'COM_HOTSPOTS_CONTACT_AUTHOR_MAIL_SENT' : 'COM_HOTSPOTS_CONTACT_AUTHOR_MAIL_SENT_UNSUCCESSFUL'), $sent ? 'message' : 'error');
    }

    /**
     * Sends an HTML mail from the site address with Reply-To set to the visitor.
     */
    private function mail(string $to, string $subject, string $body, string $replyTo, string $replyToName): bool
    {
        try {
            $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $mailer->setSender([$this->app->get('mailfrom'), $this->app->get('fromname')]);
            $mailer->addRecipient($to);
            $mailer->addReplyTo($replyTo, $replyToName);
            $mailer->setSubject($subject);
            $mailer->isHtml(true);
            $mailer->setBody($body);

            return (bool) $mailer->Send();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sentTimes(): array
    {
        return array_values(array_filter(
            (array) $this->app->getSession()->get(self::SESSION_KEY, []),
            static fn ($time): bool => (int) $time > time() - 3600
        ));
    }

    private function withinLimit(): bool
    {
        return \count($this->sentTimes()) < self::MAX_PER_HOUR;
    }

    private function countMail(): void
    {
        $times   = $this->sentTimes();
        $times[] = time();

        $this->app->getSession()->set(self::SESSION_KEY, $times);
    }

    /**
     * Answers JSON and ends the request.
     */
    private function json(mixed $data): never
    {
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $this->app->sendHeaders();

        echo json_encode($data);

        $this->app->close();
    }
}
