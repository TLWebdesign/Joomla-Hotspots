<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

/**
 * Shared XML and escaping helpers for the custom field types.
 */
abstract class AbstractType implements CustomfieldTypeInterface
{
    /**
     * Attributes of every field: name, label, default and required.
     */
    protected function baseAttributes(object $field): string
    {
        return 'name="' . self::attr($field->slug) . '" label="' . self::attr($field->title) . '" default="'
            . self::attr($field->default ?? '') . '" required="' . (empty($field->allow_empty) ? 'true' : 'false') . '"';
    }

    protected static function attr(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * "key=value" lines.
     *
     * @return  array<string, string>
     */
    protected static function optionsArray(?string $options): array
    {
        $array = [];

        foreach (preg_split('/\R/', (string) $options) as $line) {
            $option = explode('=', $line, 2);

            if (\count($option) === 2) {
                $array[trim($option[0])] = trim($option[1]);
            }
        }

        return $array;
    }

    /**
     * JSON options (advanced list): {"options": [{"value": .., "label": ..}], other attributes}.
     */
    protected static function optionsJson(?string $options): object
    {
        $json = json_decode((string) $options);

        if (!\is_object($json)) {
            throw new \RuntimeException('The options of this custom field are not valid JSON.', 500);
        }

        return $json;
    }

    protected static function translate(string $value): string
    {
        return self::e(Text::_($value));
    }
}
