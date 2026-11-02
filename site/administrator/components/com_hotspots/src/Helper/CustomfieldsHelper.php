<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Database\DatabaseInterface;
use TLWebdesign\Component\Hotspots\Administrator\Customfield\AdvancedlistType;
use TLWebdesign\Component\Hotspots\Administrator\Customfield\CheckboxType;
use TLWebdesign\Component\Hotspots\Administrator\Customfield\CustomfieldTypeInterface;
use TLWebdesign\Component\Hotspots\Administrator\Customfield\ListType;
use TLWebdesign\Component\Hotspots\Administrator\Customfield\TextType;
use TLWebdesign\Component\Hotspots\Administrator\Customfield\UrlType;

\defined('_JEXEC') or die;

/**
 * Hotspots' own custom fields (#__hotspots_customfields). A marker stores its values as JSON in
 * #__hotspots_marker.customfields, keyed by the field slug.
 */
abstract class CustomfieldsHelper
{
    public const COMPONENT = 'com_hotspots.hotspot';

    private const TYPES = [
        'text'         => TextType::class,
        'checkbox'     => CheckboxType::class,
        'list'         => ListType::class,
        'advancedlist' => AdvancedlistType::class,
        'url'          => UrlType::class,
    ];

    /** @var array<string, CustomfieldTypeInterface> */
    private static array $instances = [];

    /** @var array<string, array> */
    private static array $cache = [];

    /**
     * The field types for the "type" select.
     *
     * @return  string[]
     */
    public static function getTypes(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * Enabled fields for markers in these categories: fields shown in all categories plus the fields of
     * these categories, in field ordering, keyed by slug. Without categories only the "all" fields.
     * Each field's params is decoded (object or null).
     *
     * @param   int|int[]  $catids
     *
     * @return  array<string, object>
     */
    public static function getFields(int|array $catids = []): array
    {
        $catids = array_values(array_unique(array_filter(array_map('intval', (array) $catids))));
        $key    = implode(',', $catids);

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db        = Factory::getContainer()->get(DatabaseInterface::class);
        $component = self::COMPONENT;
        $query     = $db->getQuery(true)
            ->select('f.*')
            ->from($db->quoteName('#__hotspots_customfields', 'f'))
            ->where($db->quoteName('f.enabled') . ' = 1')
            ->where($db->quoteName('f.component') . ' = :component')
            ->bind(':component', $component);

        if ($catids) {
            $sub = $db->getQuery(true)
                ->select($db->quoteName('c.customfield_id'))
                ->from($db->quoteName('#__hotspots_customfields_cats', 'c'))
                // The subquery goes into the outer query as a string, so its values are inlined (ints only).
                ->where($db->quoteName('c.catid') . ' IN (' . implode(',', $catids) . ')');

            $query->where('(' . $db->quoteName('f.show') . ' = ' . $db->quote('all')
                . ' OR (' . $db->quoteName('f.show') . ' = ' . $db->quote('category')
                . ' AND ' . $db->quoteName('f.id') . ' IN (' . $sub . ')))');
        } else {
            $query->where($db->quoteName('f.show') . ' = ' . $db->quote('all'));
        }

        $query->order($db->quoteName('f.ordering') . ' ASC')->order($db->quoteName('f.id') . ' ASC');

        $fields = [];

        foreach ($db->setQuery($query)->loadObjectList() as $field) {
            $field->params        = json_decode((string) $field->params);
            $fields[$field->slug] = $field;
        }

        return self::$cache[$key] = $fields;
    }

    /**
     * Joomla form XML for the fields, in the group "customfields".
     *
     * @param   object[]  $fields
     */
    public static function formXml(array $fields): string
    {
        $xml = [];

        foreach ($fields as $field) {
            $type = self::type((string) $field->type);

            if ($type) {
                $xml[] = $type->xml($field);
            }
        }

        return '<form><fields name="customfields"><fieldset name="customfields">' . implode('', $xml) . '</fieldset></fields></form>';
    }

    /**
     * Adds the custom fields of these categories to a marker form and binds the marker's values.
     *
     * @param   int|int[]           $catids
     * @param   string|array|null   $values  The marker's customfields (JSON string or array).
     */
    public static function addToForm(Form $form, int|array $catids, string|array|null $values = null): void
    {
        $fields = self::getFields($catids);

        if (!$fields) {
            return;
        }

        $form->load(self::formXml($fields));

        if ($values !== null) {
            $form->bind(['customfields' => \is_array($values) ? $values : (json_decode($values, true) ?: [])]);
        }
    }

    /**
     * Escaped HTML for a stored value (labels for list options, links for URLs).
     */
    public static function render(object $field, mixed $value): string
    {
        $type = self::type((string) $field->type);

        if (!$type) {
            return htmlspecialchars(\is_array($value) ? implode(', ', $value) : (string) $value, ENT_QUOTES, 'UTF-8');
        }

        try {
            return $type->render($field, $value);
        } catch (\RuntimeException) {
            return htmlspecialchars(\is_array($value) ? implode(', ', $value) : (string) $value, ENT_QUOTES, 'UTF-8');
        }
    }

    private static function type(string $type): ?CustomfieldTypeInterface
    {
        $type = strtolower($type);

        if (!isset(self::TYPES[$type])) {
            return null;
        }

        return self::$instances[$type] ??= new (self::TYPES[$type])();
    }
}
