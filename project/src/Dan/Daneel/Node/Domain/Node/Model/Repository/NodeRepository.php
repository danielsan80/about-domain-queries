<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Repository;

use Dan\Daneel\Node\Domain\Node\Model\Node;

interface NodeRepository
{
    public function add(Node $node): void;

    public function remove(Node $node): void;
}
