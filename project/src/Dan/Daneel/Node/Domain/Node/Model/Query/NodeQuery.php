<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Query;

use Assert\Assertion;

class NodeQuery
{
    const LABEL = 'label';
    const POSITION = 'position';
    const ASC = 'asc';
    const DESC = 'desc';

    private $data = [];

    // CONSTRUCTION

    private function __construct()
    {
    }

    public static function create(array $data = []): self
    {
        $data = self::_clear($data);

        $query = self::_create();

        $query = $query->slice($data['start'], $data['length']);

        if ($data['sort_key']) {
            $query = $query->sort($data['sort_key'], $data['sort_direction']);
        }

        if ($data['root']) {
            $query = $query->setRootOnly();
        }

        if ($data['parent_id']) {
            $query = $query->setParentId($data['parent_id']);
        }

        return $query;
    }


    public static function fromQuery(?self $query = null): self
    {
        if ($query) {
            return $query;
        }
        return self::create();
    }

    // NORMALIZATION

    public function toArray(): array
    {
        return array_filter($this->data, function ($item) {
            return $item !== null;
        });
    }

    // SLICING

    public function start(): ?int
    {
        return $this->_get('start');
    }

    public function length(): ?int
    {
        return $this->_get('length');
    }

    public function setStart(?int $start): self
    {
        Assertion::nullOrGreaterOrEqualThan($start, 0);
        return $this->_set('start', $start);
    }

    public function setLength(?int $length): self
    {
        Assertion::nullOrGreaterOrEqualThan($length, 1);
        return $this->_set('length', $length);
    }

    public function removeStart(): self
    {
        return $this->_unset('start');
    }

    public function removeLength(): self
    {
        return $this->_unset('length');
    }

    public function slice(?int $start, ?int $length): self
    {
        return $this->setStart($start)->setLength($length);
    }

    public function removeSlice(): self
    {
        return $this->removeStart()->removeLength();
    }

    // SORTING

    public function sortKey(): ?string
    {
        return $this->_get('sort_key');
    }

    public function sortDirection(): ?string
    {
        return $this->_get('sort_direction');
    }

    public function setSortKey(string $sortKey): self
    {
        $availableSortKeys = self::_availableSortKeys();
        Assertion::inArray(
            $sortKey,
            $availableSortKeys,
            sprintf(
                'Sort key "%s" is unknown. Available ones are [%s]', $sortKey, implode(', ', $availableSortKeys)
            ));
        return $this->_set('sort_key', $sortKey);
    }

    public function setSortDirection(string $sortDirection): self
    {
        $availableSortDirections = [self::ASC, self::DESC];
        Assertion::inArray(
            $sortDirection,
            $availableSortDirections,
            sprintf(
                'Sort direction "%s" is unknown. Available ones are [%s]', $sortDirection, implode(', ', $availableSortDirections)
            ));
        return $this->_set('sort_direction', $sortDirection);
    }

    public function removeSortKey(): self
    {
        return $this->_unset('sort_key');
    }

    public function removeSortDirection(): self
    {
        return $this->_unset('sort_direction');
    }

    public function sort(string $sortKey, ?string $sortDirection = null): self
    {
        $query = $this->setSortKey($sortKey);
        if ($sortDirection === null) {
            $query = $query->removeSortDirection();
        } else {
            $query = $query->setSortDirection($sortDirection);
        }
        return $query;
    }

    public function removeSort(): self
    {
        return $this->_unset('sort_key')->_unset('sort_direction');
    }

    public function sortDefault(): self
    {
        return $this->sort(self::POSITION);
    }

    // SPECIFIC CRITERIA

    public function isRootOnly(): bool
    {
        return $this->_get('root', false);
    }

    public function parentId(): ?string
    {
        return $this->_get('parent_id');
    }

    public function setRootOnly(): self
    {
        Assertion::null($this->_get('parent_id'), '"root" is not compatible with "parent_id"');
        return $this->_set('root', true);
    }

    public function setParentId(string $parentId): self
    {
        Assertion::null($this->_get('root'), '"parent_id" is not compatible with "root"');
        return $this->_set('parent_id', $parentId);
    }

    public function removeRootOnly(): self
    {
        return $this->_unset('root');
    }

    public function removeParentId(): self
    {
        return $this->_unset('parent_id');
    }


    public function setParentIdOrRoot(?string $parentId): self
    {
        if ($parentId === null) {
            return $this->setRootOnly();
        }
        return $this->setParentId($parentId);

    }

    // PRIVATE UTILITIES

    private static function _clear(array $data = []): array
    {
        Assertion::allInArray(array_keys($data), array_keys(static::_defaults()));
        return array_replace(static::_defaults(), $data);
    }

    private static function _create(): self
    {
        return new self();
    }

    private function _get(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->data)) {
            return $default;
        }
        return $this->data[$key];
    }

    private function _set(string $key, $value): self
    {
        $query = new self();
        $query->data = $this->toArray();
        $query->data[$key] = $value;
        return $query;
    }

    protected function _unset(string $key): self
    {
        $query = new self();
        $query->data = $this->toArray();
        unset($query->data[$key]);
        return $query;
    }

    // CONFIGURATION

    protected static function _availableSortKeys(): array
    {
        return [self::POSITION, self::LABEL];
    }

    private static function _defaults(): array
    {
        return [
            'start' => null,
            'length' => null,
            'sort_key' => null,
            'sort_direction' => null,
            'root' => null,
            'parent_id' => null,
        ];
    }

}
