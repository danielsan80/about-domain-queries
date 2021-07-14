<?php
declare(strict_types=1);

namespace Dan\Golan\Node\Domain\Node\Model\Provider\Infrastructure\InMemory;

use Assert\Assertion;
use Dan\Golan\Node\Domain\Common\Model\Code;
use Dan\Golan\Node\Domain\Common\Model\Provider\Infrastructure\InMemory\InMemoryProviderUtil;
use Dan\Golan\Node\Domain\Node\Model\Id\NodeId;
use Dan\Golan\Node\Domain\Node\Model\Node;
use Dan\Golan\Node\Domain\Node\Model\Provider\NodeProvider;
use Dan\Golan\Node\Domain\Node\Model\Query\NodeQuery;

class InMemoryNodeProvider implements NodeProvider
{

    private $nodes;

    public function __construct(array $nodes)
    {
        Assertion::allIsInstanceOf($nodes, Node::class);
        $this->nodes = $nodes;
    }

    public function byId(?NodeId $id): ?Node
    {
        if (!$id) {
            return null;
        }
        foreach ($this->nodes as $node) {
            if ($node->id()->equals($id)) {
                return $node;
            }
        }
        return null;
    }

    public function byCode(Code $code): ?Node
    {
        foreach ($this->nodes as $node) {
            if ($node->code()->equals($code)) {
                return $node;
            }
        }
        return null;
    }

    public function byQuery(?NodeQuery $query = null): array
    {
        $query = NodeQuery::fromQuery($query);
        $nodes = $this->nodes;
        if ($query->isRootOnly()) {
            $nodes = array_filter($nodes, function (Node $node) {
                return !$node->parentId();
            });
        }
        if ($query->parentId()) {
            $nodes = array_filter($nodes, function (Node $node) use ($query) {
                return (new NodeId($query->parentId()))->equals($node->parentId());
            });
        }

        $this->sort($nodes, $query);
        return InMemoryProviderUtil::slice($nodes, $query->start(), $query->length());
    }

    private function sort(array &$nodes, NodeQuery $query): void
    {
        InMemoryProviderUtil::sortByQuery($nodes, $query, [
            NodeQuery::POSITION => 'position',
        ]);
    }
}
