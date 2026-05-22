<?php

namespace App\Models\Concerns;

trait HasMenuTranslations
{
    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if ($locale !== 'tr') {
            $key = 'name_'.$locale;
            $value = $this->{$key} ?? null;
            if (filled($value)) {
                return $value;
            }
        }

        return (string) $this->name;
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        if (! isset($this->description)) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();

        if ($locale !== 'tr') {
            $key = 'description_'.$locale;
            $value = $this->{$key} ?? null;
            if (filled($value)) {
                return $value;
            }
        }

        return $this->description;
    }
}
