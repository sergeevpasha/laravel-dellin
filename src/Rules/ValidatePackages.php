<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidatePackages implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param array|string $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $package) {
                if (!isset($package['uid'])) {
                    return false;
                }
                if (isset($package['count'])) {
                    if (!is_numeric($package['count'])) {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('dellin::messages.wrong_package');
    }
}
