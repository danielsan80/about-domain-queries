<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model;

use Assert\Assertion;

class Code implements \JsonSerializable
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        Assertion::regex(
            $value,
            '/^[a-z0-9_\-]+$/',
            '"%s" is not a valid code. Only alphanumeric chars and ["_","-"] are allowed'
        );

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function equals(self $code): bool
    {
        return $this->value === (string)$code;
    }

}
