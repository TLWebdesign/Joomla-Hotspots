<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Customfields;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;

\defined('_JEXEC') or die;

/**
 * The custom fields of a category as form HTML, loaded into the marker form when the category changes:
 * index.php?option=com_hotspots&view=customfields&format=raw&catid=<category>&id=<marker>
 */
class RawView extends AbstractView
{
    public function display($tpl = null)
    {
        $input  = Factory::getApplication()->getInput();
        $values = null;
        $id     = $input->getInt('id', 0);

        if ($id) {
            $marker = self::loadMarker($id);

            if (!$marker || !SecurityHelper::authorise('edit', $marker)) {
                throw new \RuntimeException('The resource does not exist', 404);
            }

            $values = (string) $marker->customfields;
        }

        echo self::renderFields($input->getInt('catid', 0), $values);
    }

    /**
     * Label and input of each custom field of the category, bound to the marker's values.
     */
    public static function renderFields(int $catid, ?string $values = null): string
    {
        $form = new Form('com_hotspots.customfields', ['control' => 'jform']);

        CustomfieldsHelper::addToForm($form, $catid ? [$catid] : [], $values);

        $html = '';

        foreach ($form->getGroup('customfields') as $field) {
            $html .= $field->renderField();
        }

        return $html;
    }

    private static function loadMarker(int $id): ?object
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'catid', 'created_by', 'customfields']))
            ->from($db->quoteName('#__hotspots_marker'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObject() ?: null;
    }
}
