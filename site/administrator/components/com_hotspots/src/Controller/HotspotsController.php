<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Service\Geocoder;

\defined('_JEXEC') or die;

/**
 * List tasks for markers: publish, unpublish, delete (from AdminController) and geocode.
 */
class HotspotsController extends AdminController
{
    protected $text_prefix = 'COM_HOTSPOTS';

    public function getModel($name = 'Hotspot', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Markers without coordinates get them from their address, markers with coordinates get the address.
     * AJAX, answers JSON.
     */
    public function geocode(): void
    {
        if (!$this->checkToken('post', false)) {
            $this->sendJson(new JsonResponse(null, Text::_('JINVALID_TOKEN'), true));

            return;
        }

        $ids = array_filter(ArrayHelper::toInteger($this->input->post->get('cid', [], 'array')));

        if (!$ids) {
            $this->sendJson(new JsonResponse(null, Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), true));

            return;
        }

        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__hotspots_marker'))
            ->whereIn($db->quoteName('id'), $ids);
        $markers = $db->setQuery($query)->loadObjectList();

        $geocoder = new Geocoder();
        $user     = $this->app->getIdentity();
        $updated  = 0;
        $failed   = 0;

        foreach ($markers as $marker) {
            if (!SecurityHelper::authorise('edit', $marker, $user)) {
                $failed++;
                continue;
            }

            $fields = [];

            if ((float) $marker->gmlat != 0.0 && (float) $marker->gmlng != 0.0) {
                $data = $geocoder->reverseGeocode((float) $marker->gmlat, (float) $marker->gmlng);

                if (\is_array($data) && $data['status'] === 'OK') {
                    $fields = array_intersect_key($data['address'], array_flip(['street', 'plz', 'town', 'country']));
                }
            } else {
                $address = implode(',', array_filter([$marker->street, $marker->plz, $marker->town, $marker->country]));
                $data    = $address !== '' ? $geocoder->geocode($address) : false;

                if (\is_array($data) && $data['status'] === 'OK') {
                    $fields = ['gmlat' => (float) $data['location']->lat, 'gmlng' => (float) $data['location']->lng];
                }
            }

            if (!$fields) {
                $failed++;
                continue;
            }

            $id     = (int) $marker->id;
            $update = $db->getQuery(true)
                ->update($db->quoteName('#__hotspots_marker'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);

            foreach ($fields as $column => $value) {
                $update->set($db->quoteName($column) . ' = ' . $db->quote((string) $value));
            }

            try {
                $db->setQuery($update)->execute();
                $updated++;
            } catch (\RuntimeException $e) {
                $failed++;
            }
        }

        $message = $updated
            ? Text::sprintf('COM_HOTSPOTS_GEOCODING_COORDINATES_UPDATED', $updated)
            : Text::_('COM_HOTSPOTS_GEOCODING_NOTHING_TO_UPDATE');

        if ($failed) {
            $message .= ' ' . Text::plural('COM_HOTSPOTS_GEOCODING_N_FAILED', $failed);
        }

        $this->sendJson(new JsonResponse(['updated' => $updated, 'failed' => $failed], $message));
    }

    private function sendJson(JsonResponse $response): void
    {
        $this->app->mimeType = 'application/json';
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->app->sendHeaders();
        echo $response;
        $this->app->close();
    }
}
