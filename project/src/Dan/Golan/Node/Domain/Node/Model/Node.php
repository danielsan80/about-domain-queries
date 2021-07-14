<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Node\Model;

use Dan\Golan\Node\Domain\Common\Model\Code;
use Dan\Golan\Node\Domain\Common\Model\LocalizedString;
use Dan\Golan\Node\Domain\Node\Model\Id\NodeId;

class Node
{
    private $id;
    private $code;
    private $label;
    private $position;

    public function __construct(
        NodeId $id,
        Code $code,
        LocalizedString $label,
        int $position
    )
    {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
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

    public function label(): LocalizedString
    {
        return $this->label;
    }

    public function position(): int
    {
        return $this->position;
    }

}
