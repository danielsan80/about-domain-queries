<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Node\Model\Id;

use Dan\Golan\Node\Domain\Common\Model\Id\Id;

class NodeId extends Id
{
    public static function create(): self
    {
        return self::doCreate();
    }

    public function equals(?self $id): bool
    {
        return $this->doEquals($id);
    }
}
