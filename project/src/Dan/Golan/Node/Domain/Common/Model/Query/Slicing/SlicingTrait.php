<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Query\Slicing;


use Dan\Golan\Node\Domain\Common\Model\Query\AbstractQuery;

class SlicingTrait
{
    protected static function _defaultsSlicing(): array
    {
        return [
            'start' => null,
            'length' => null,
        ];
    }

    protected static function _addSlicing(AbstractQuery $query, array $data): AbstractQuery
    {
        return $query->slice($data['start'], $data['length']);
    }

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
}
