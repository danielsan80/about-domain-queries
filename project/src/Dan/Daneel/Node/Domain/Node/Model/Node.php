<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model;

use Dan\Daneel\Node\Domain\Common\Model\LocalizedString;
use Dan\Daneel\Node\Domain\Node\Model\Code\Code;
use Dan\Daneel\Node\Domain\Node\Model\Id\NodeId;

class Node
{
    private $id;
    private $code;
    private $label;
    private $parentId;
    private $position;

    public function __construct(
        NodeId $id,
        Code $code,
        string $label,
        ?NodeId $parentId,
        int $position = 0
    )
    {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->parentId = $parentId;
        $this->position = $position;
    }

    public function id(): NodeId
    {
        return $this->id;
    }

    public function code(): Code
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function parentId(): ?NodeId
    {
        return $this->parentId;
    }

    public function position(): int
    {
        return $this->position;
    }
}
