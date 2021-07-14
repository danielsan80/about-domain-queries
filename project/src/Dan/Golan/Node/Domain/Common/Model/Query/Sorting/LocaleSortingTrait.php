<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Query\Sorting;


use Dan\Golan\Node\Domain\Common\Model\Query\AbstractQuery;

trait LocaleSortingTrait
{
    protected static function _defaultsLocaleSorting(): array
    {
        return [
            'sort_locale' => null,
        ];
    }

    protected static function _addLocaleSorting(AbstractQuery $query, array $data): AbstractQuery
    {
        if (!$data['sort_locale']) {
            return $query;
        }
        return $query->setSortLocale($data['sort_locale']);
    }

    public function sortLocale(): ?string
    {
        return $this->_get('sort_locale');
    }

    public function setSortLocale(string $sortLocale): self
    {
        return $this->_set('sort_locale', $sortLocale);
    }

    public function removeSortLocale(): self
    {
        return $this->_unset('sort_locale');
    }
}
