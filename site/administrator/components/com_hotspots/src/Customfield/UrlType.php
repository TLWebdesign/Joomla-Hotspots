<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

class UrlType extends AbstractType
{
    public function xml(object $field): string
    {
        return '<field type="url" ' . $this->baseAttributes($field) . ' filter="url" validate="url" schemes="http,https,mailto" class="form-control"/>';
    }

    public function render(object $field, mixed $value): string
    {
        $value = trim((string) (\is_array($value) ? reset($value) : $value));

        if ($value === '') {
            return '';
        }

        // "www.example.com" means an external site.
        if (str_starts_with(strtolower($value), 'www.')) {
            $value = 'https://' . $value;
        }

        $uri    = new Uri($value);
        $scheme = strtolower((string) $uri->getScheme());

        // Frontend users fill this in: no javascript: or data: links.
        if ($scheme !== '' && !\in_array($scheme, ['http', 'https', 'mailto'], true)) {
            return self::e($value);
        }

        if ($scheme === 'mailto') {
            $address = (string) $uri->getPath();

            return '<a href="mailto:' . self::e($address) . '">' . self::e($address) . '</a>';
        }

        $display = $uri->getHost() ?: $uri->toString();

        return '<a href="' . self::e($uri->toString()) . '" target="_blank" rel="nofollow noopener noreferrer">' . self::e($display) . '</a>';
    }
}
