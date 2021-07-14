<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Query;

use Assert\Assertion;

abstract class AbstractQuery
{
    private $data = [];

    private function __construct()
    {
    }

    protected static function _clear(array $data = []): array
    {
        Assertion::allInArray(array_keys($data), array_keys(static::_defaults()));
        return array_replace(static::_defaults(), $data);
    }

    protected static function _create()
    {
        return new static();
    }

    abstract protected static function _defaults(): array;

    public function toArray(): array
    {
        return array_filter($this->data, function ($item) {
            return $item !== null;
        });
    }

    protected function _get(string $key, $default=null)
    {
        if (!array_key_exists($key, $this->data)) {
            return $default;
        }
        return $this->data[$key];
    }

    protected function _set(string $key, $value): self
    {
        $query = new static();
        $query->data = $this->toArray();
        $query->data[$key] = $value;
        return $query;
    }

    protected function _unset(string $key): self
    {
        $query = new static();
        $query->data = $this->toArray();
        unset($query->data[$key]);
        return $query;
    }

}
