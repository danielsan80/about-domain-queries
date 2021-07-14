<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Provider;

use Dan\Daneel\Node\Domain\Node\Model\Code\Code;
use Dan\Daneel\Node\Domain\Node\Model\Id\NodeId;
use Dan\Daneel\Node\Domain\Node\Model\Node;
use Dan\Daneel\Node\Domain\Node\Model\Query\NodeQuery;

interface NodeProvider
{

    public function byId(?NodeId $id): ?Node;

    public function byCode(Code $code): ?Node;

    public function byQuery(?NodeQuery $query = null): array;
}
