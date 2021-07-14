<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Common\Model\Provider\Infrastructure\InMemory;

class Criterion
{
    const ASC = 'asc';
    const DESC = 'desc';

    private $method;
    private $direction;
    private $locale;

    public function __construct(
        string $method,
        ?string $direction = null,
        ?string $locale = null
    )
    {
        $this->method = $method;
        $this->direction = $direction;
        $this->locale = $locale;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function direction(): ?string
    {
        return $this->direction;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }
}
