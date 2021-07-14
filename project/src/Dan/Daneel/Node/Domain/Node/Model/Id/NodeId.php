<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Id;

use Assert\Assertion;
use Ramsey\Uuid\Uuid as RamseyUuid;

class NodeId implements \JsonSerializable
{
    protected $uuid;

    public function __construct(string $uuid)
    {
        Assertion::uuid($uuid);

        $this->uuid = $uuid;
    }

    public function __toString(): string
    {
        return $this->uuid;
    }

    public function jsonSerialize()
    {
        return $this->uuid;
    }

    public function equals(?self $id): bool
    {
        if ($id === null) {
            return false;
        }
        return $this->uuid === (string)$id;
    }

    public static function create(): self
    {
        return new static((string)RamseyUuid::uuid4());
    }
}
