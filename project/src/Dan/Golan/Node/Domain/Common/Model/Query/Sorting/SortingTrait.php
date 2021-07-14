<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Query\Sorting;

use Assert\Assertion;
use Dan\Golan\Node\Domain\Common\Model\Query\AbstractQuery;

class SortingTrait
{

    protected static function _defaultsSorting(): array
    {
        return [
            'sort_key' => null,
            'sort_direction' => null,
        ];
    }

    protected static function _addSorting(AbstractQuery $query, array $data): AbstractQuery
    {
        if (!$data['sort_key']) {
            return $query;
        }
        return $query->sort($data['sort_key'], $data['sort_direction']);
    }

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
        $availableSortDirections = [SortingQuery::ASC, SortingQuery::DESC];
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
}
