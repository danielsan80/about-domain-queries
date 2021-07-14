<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure\InMemory;

use Assert\Assertion;
use Dan\Daneel\Node\Domain\Node\Model\Code\Code;
use Dan\Daneel\Node\Domain\Node\Model\Id\NodeId;
use Dan\Daneel\Node\Domain\Node\Model\Node;
use Dan\Daneel\Node\Domain\Node\Model\Provider\NodeProvider;
use Dan\Daneel\Node\Domain\Node\Model\Query\NodeQuery;

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

        $nodes = $this->sort($nodes, $query);
        return $this->slice($nodes, $query->start(), $query->length());
    }

    private function sort(array $nodes, NodeQuery $query): array
    {
        $sortKeys = [
            NodeQuery::POSITION => 'position',
            NodeQuery::LABEL => 'label',
        ];

        foreach ($sortKeys as $sortKey => $method) {
            if ($query->sortKey() === $sortKey) {
                $direction = $query->sortDirection() === NodeQuery::DESC ? NodeQuery::DESC : NodeQuery::ASC;
                usort($nodes, self::byMethod($method, $direction));
            }
        }

        return $nodes;
    }

    public static function slice(array $items, ?int $start = null, ?int $length = null): array
    {
        $start = $start !== null ? $start : 0;
        $items = array_slice($items, $start, $length);
        return array_values($items);
    }

    private static function byMethod(string $method, string $direction): callable
    {
        return function (Node $a, Node $b) use ($method, $direction) {
            if ($a->$method() == $b->$method()) {
                return 0;
            }

            $result = ($a->$method() < $b->$method()) ? -1 : 1;
            if ($direction === NodeQuery::DESC) {
                $result *= -1;
            }
            return $result;
        };
    }
}
