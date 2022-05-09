<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Helpers;

class DellinHelper
{
    /**
     * Remove null values from an array recursively
     *
     * @param  array $haystack
     * @return array
     */
    public static function removeNullValues(array $haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = self::removeNullValues($haystack[$key]);
            }
            if (empty($haystack[$key]) && $haystack[$key] !== false) {
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }
}
