<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

\defined('_JEXEC') or die;

/**
 * Image upload for markers. Replaces lib_compojoom's "cmultimedia".
 *
 * <field name="picture" type="multimedia" url="index.php?option=com_hotspots&amp;task=multimedia.doIt"
 *        component="com_hotspots" label="..."/>
 *
 * Posts the file names as jform[picture][] and title/description as jform[picture_data][<name>][...];
 * MultimediaModel::uploadPermanent() saves them after the marker is saved.
 */
class MultimediaField extends FormField
{
    protected $type = 'Multimedia';

    protected function getInput()
    {
        $component = (string) ($this->element['component'] ?? 'com_hotspots') ?: 'com_hotspots';
        $user      = Factory::getApplication()->getIdentity();

        if (!$user || !$user->authorise('core.multimedia.create', $component)) {
            return '';
        }

        $params    = ComponentHelper::getParams($component);
        $imageSize = explode('x', (string) $params->get('thumbs.original', '2400x1800')) + [1 => 1800];
        $token     = Session::getFormToken() . '=1';
        $url       = (string) ($this->element['url'] ?? '') ?: 'index.php?option=' . $component . '&task=multimedia.doIt';
        $id        = Factory::getApplication()->getInput()->getInt('id', (int) $this->form->getValue('id'));
        $fileTypes = array_values(array_intersect(
            array_map('strtolower', array_map('trim', explode(',', (string) $params->get('upload_extensions', 'gif,jpg,jpeg,png')))),
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        ));

        return LayoutHelper::render(
            'multimedia.fileupload',
            [
                'formControl'      => $this->formControl,
                'fieldName'        => $this->fieldname,
                'url'              => $url . '&' . $token,
                'existingUrl'      => $url . '&' . $token . '&id=' . $id,
                'maxNumberOfFiles' => (int) ($this->element['maxNumberOfFiles'] ?? 0) ?: (int) $params->get('max_number_of_files', 10),
                'maxSize'          => (float) $params->get('upload_maxsize', 10),
                'fileTypes'        => array_values(array_unique($fileTypes)),
                'imageSize'        => ['x' => (int) $imageSize[0], 'y' => (int) $imageSize[1]],
                'canDelete'        => $user->authorise('core.multimedia.delete', $component) || $user->authorise('core.multimedia.delete.own', $component),
            ],
            JPATH_ADMINISTRATOR . '/components/com_hotspots/layouts'
        );
    }

    protected function getLabel()
    {
        $component = (string) ($this->element['component'] ?? 'com_hotspots') ?: 'com_hotspots';
        $user      = Factory::getApplication()->getIdentity();

        if (!$user || !$user->authorise('core.multimedia.create', $component)) {
            return '';
        }

        return parent::getLabel();
    }
}
