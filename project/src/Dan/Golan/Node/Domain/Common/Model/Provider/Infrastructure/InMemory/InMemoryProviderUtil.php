<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Provider\Infrastructure\InMemory;

use Dan\Golan\Node\Domain\Common\Model\LocalizedString;
use Dan\Golan\Node\Domain\Common\Model\Query\Sorting\SortingQuery;

class InMemoryProviderUtil
{
    const ASC = 'asc';
    const DESC = 'desc';

    public static function sort(array &$items, string $method, ?string $direction = self::ASC): array
    {
        $direction = $direction === self::DESC ? self::DESC : self::ASC;
        usort($items, self::byMethod($method, $direction));
        return $items;
    }

    public static function sortByLocalizedString(array &$items, string $method, string $locale, ?string $direction = self::ASC): array
    {
        $direction = $direction === self::DESC ? self::DESC : self::ASC;
        usort($items, self::byLocalizedString($method, $locale, $direction));
        return $items;
    }

    public static function sortByQuery(array &$items, $query, array $sortKeys): void
    {
        foreach ($sortKeys as $sortKey => $method) {
            if ($query->sortKey() === $sortKey) {
                $direction = $query->sortDirection() === SortingQuery::DESC ? self::DESC : self::ASC;
                self::sort($items, $method, $direction);
            }
        }
    }

    public static function sortByCriteria(array &$items, array $criteria): void
    {
        usort($items, self::byCriteria($criteria));
    }

    public static function slice(array $items, ?int $start = null, ?int $length = null): array
    {
        $start = $start !== null ? $start : 0;
        $items = array_slice($items, $start, $length);
        return array_values($items);
    }

    public static function byMethod(string $method, string $direction): callable
    {
        return function ($a, $b) use ($method, $direction) {
            if ($a->$method() == $b->$method()) {
                return 0;
            }

            $result = ($a->$method() < $b->$method()) ? -1 : 1;
            if ($direction === self::DESC) {
                $result *= -1;
            }
            return $result;
        };
    }

    public static function byCriteria(array $criteria): callable
    {
        return function ($a, $b) use ($criteria) {
            while (true) {
                if (!$criteria) {
                    return 0;
                }
                $criterion = array_shift($criteria);
                $method = $criterion->method();

                $aValue = $a->$method();
                if ($aValue instanceof LocalizedString) {
                    $aValue = $aValue->value($criterion->locale());
                }
                $bValue = $b->$method();
                if ($bValue instanceof LocalizedString) {
                    $bValue = $bValue->value($criterion->locale());
                }

                if ($aValue == $bValue) {
                    continue;
                }

                $result = $aValue < $bValue? -1: 1;

                if ($criterion->direction() === self::DESC) {
                    $result *= -1;
                }

                return $result;
            }
        };
    }

    public static function byLocalizedString(string $method, string $locale, string $direction): callable
    {
        return function ($a, $b) use ($method, $locale, $direction) {
            if ($a->$method()->value($locale) == $b->$method()->value($locale)) {
                return 0;
            }

            $result = ($a->$method()->value($locale) < $b->$method()->value($locale)) ? -1 : 1;
            if ($direction === self::DESC) {
                $result *= -1;
            }
            return $result;
        };
    }


    public static function notImplemented()
    {
        throw new \LogicException('Not implemented yet');

    }
}
