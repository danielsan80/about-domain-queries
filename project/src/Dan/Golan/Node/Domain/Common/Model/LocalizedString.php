<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model;

use Assert\Assertion;

class LocalizedString
{
    const DEFAULT_LOCALE = 'en-GB';

    private $values;

    public function __construct(array $values)
    {
        Assertion::notEmpty($values);

        array_walk($values, function ($value, $locale) {
            Assertion::string($value, 'Only string values are allowed');
            Assertion::notBlank($value, sprintf('The value for locale "%s" is empty.', $locale));
        });

        $this->values = $values;
    }


    public function hasLocale(string $locale): bool
    {
        return array_key_exists($locale, $this->values);
    }

    public function value(string $locale, ?string $default = null): ?string
    {
        if ($this->hasLocale($locale)) {
            return $this->values[$locale];
        }

        return $default;
    }

    static public function fromArray(array $values): self
    {
        return new self($values);
    }

    static public function fromString(string $value): self
    {
        return new static([
            self::DEFAULT_LOCALE => $value,
        ]);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function __toString()
    {

        if (count($this->values) === 1) {
            foreach ($this->values as $text) {
                return $text;
            }
        }

        if (isset($this->values[self::DEFAULT_LOCALE])) {
            return($this->values[self::DEFAULT_LOCALE]);
        }

        return json_encode($this->values);
    }

    public function equals(self $localizedString): bool
    {
        return $this->toArray() == $localizedString->toArray();
    }

}
