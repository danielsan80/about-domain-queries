<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\Fixtures;

use Dan\Daneel\Node\Domain\Node\Model\Code\Code;
use Dan\Daneel\Node\Domain\Node\Model\Id\NodeId;
use Dan\Daneel\Node\Domain\Node\Model\Node;
use Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\TestUuid;
use Dan\FixtureHandler\Fixture\AbstractFixture;

class NodesFixture extends AbstractFixture
{

    public function load(): void
    {
        $nodesData = $this->getRef('data.nodes');
        $nodes = [];
        foreach ($nodesData as $data) {
            $node = new Node(
                new NodeId(TestUuid::create((int)$data['position'])),
                new Code($data['code']),
                $data['label'],
                isset($data['parent']) ? $this->getRef('node.' . $data['parent'])->id() : null,
                (int)$data['position']
            );
            $nodes[$data['code']] = $node;
            $this->setRef('node.' . $node->code(), $node);
        }
        $this->setRef('nodes', $nodes);
    }

    public function dependsOn(): array
    {
        return ['data.nodes'];
    }
}
