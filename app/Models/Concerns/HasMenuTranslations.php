<?php

namespace App\Models\Concerns;

use App\Support\MenuLocale;

trait HasMenuTranslations
{
    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (method_exists($this, 'getTranslation')) {
            $value = $this->getTranslation('name', $locale, false);

            if (filled($value)) {
                return (string) $value;
            }

            $fallback = $this->getTranslation('name', MenuLocale::DEFAULT, false);

            return filled($fallback) ? (string) $fallback : '';
        }

        return (string) ($this->name ?? '');
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        if (! isset($this->description)) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();

        if (method_exists($this, 'getTranslation')) {
            $value = $this->getTranslation('description', $locale, false);

            if (filled($value)) {
                return (string) $value;
            }

            $fallback = $this->getTranslation('description', MenuLocale::DEFAULT, false);

            return filled($fallback) ? (string) $fallback : null;
        }

        return filled($this->description) ? (string) $this->description : null;
    }
}
