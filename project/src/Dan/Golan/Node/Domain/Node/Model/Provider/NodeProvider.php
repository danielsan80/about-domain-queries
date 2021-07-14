<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Node\Model\Provider;

use Dan\Golan\Node\Domain\Common\Model\Code;
use Dan\Golan\Node\Domain\Node\Model\Id\NodeId;
use Dan\Golan\Node\Domain\Node\Model\Node;
use Dan\Golan\Node\Domain\Node\Model\Query\NodeQuery;

interface NodeProvider
{

    public function byId(?NodeId $id): ?Node;

    public function byCode(Code $code): ?Node;

    public function byQuery(?NodeQuery $query = null): array;
}
