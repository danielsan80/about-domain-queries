<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Id;

use Assert\Assertion;
use Ramsey\Uuid\Uuid as RamseyUuid;

class Id implements \JsonSerializable
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

    /**
     * If you want you can implement your `public function equals(YourId $id): bool { return $this->doEquals($id); }`
     */
    protected function doEquals($id): bool
    {
        if ($id === null) {
            return false;
        }
        return $this->uuid === (string)$id;
    }

    /**
     * If you want you can implement your `public static function create(): self { return self::doCreate(); }`
     */
    protected static function doCreate(): self
    {
        return new static((string)RamseyUuid::uuid4());
    }

}