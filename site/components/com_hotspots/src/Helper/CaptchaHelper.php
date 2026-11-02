<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Helper;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;

\defined('_JEXEC') or die;

/**
 * Captcha through Joomla's captcha plugins (option "captcha": a plugin, "" for the global default, "0" for none).
 */
abstract class CaptchaHelper
{
    /**
     * Name of the captcha plugin to use, '' when there is none.
     */
    public static function plugin(): string
    {
        $plugin = (string) SettingsHelper::get('captcha', '');

        if ($plugin === '0') {
            return '';
        }

        if ($plugin === '') {
            $plugin = (string) Factory::getApplication()->get('captcha', '0');
        }

        return ($plugin !== '' && $plugin !== '0' && PluginHelper::isEnabled('captcha', $plugin)) ? $plugin : '';
    }

    /**
     * The marker form asks the captcha only from the groups in "captcha_usergroup" (all users when empty).
     */
    public static function forMarkerForm(?User $user = null): string
    {
        $plugin = self::plugin();

        if ($plugin === '') {
            return '';
        }

        $user ??= Factory::getApplication()->getIdentity() ?? new User();
        $groups = array_filter((array) SettingsHelper::get('captcha_usergroup', []));

        return (!$groups || SecurityHelper::groupHasAccess($user->getAuthorisedGroups(), $groups)) ? $plugin : '';
    }

    public static function display(string $plugin, string $name, string $id): string
    {
        $captcha = Captcha::getInstance($plugin, ['namespace' => 'com_hotspots']);

        return $captcha ? (string) $captcha->display($name, $id, 'required') : '';
    }

    public static function check(string $plugin, ?string $answer): bool
    {
        try {
            $captcha = Captcha::getInstance($plugin, ['namespace' => 'com_hotspots']);

            return $captcha !== null && $captcha->checkAnswer($answer);
        } catch (\Exception) {
            return false;
        }
    }
}
