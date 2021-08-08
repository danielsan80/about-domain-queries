<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Provider\Testing;

use Dan\Daneel\Node\Domain\Node\Model\Code\Code;
use Dan\Daneel\Node\Domain\Node\Model\Node;
use Dan\Daneel\Node\Domain\Node\Model\Provider\NodeProvider;
use Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\Fixtures\NodesScenario;
use Dan\Daneel\Node\Domain\Node\Model\Query\NodeQuery;
use Dan\FixtureHandler\FixtureHandler;
use PHPUnit\Framework\TestCase;

abstract class NodeProviderTestCase extends TestCase
{
    protected function createFixtureHandler(): FixtureHandler
    {
        $fh = new FixtureHandler();
        $fh->addScenario(new NodesScenario());
        return $fh;
    }

    protected function getNodes(): array
    {
        $fh = $this->createFixtureHandler();
        return array_values($fh->getRef('nodes'));
    }

    protected function getNode(string $code): Node
    {
        $fh = $this->createFixtureHandler();
        return $fh->getRef('nodes')[$code];
    }

    abstract protected function createNodeProvider(): NodeProvider;

    /**
     * @test
     */
    public function sort_by_position()
    {
        $nodeProvider = $this->createNodeProvider();

        $result = $nodeProvider->byQuery(
            NodeQuery::create()
                ->sort(NodeQuery::POSITION, NodeQuery::ASC)
        );

        $this->assertEquals(
            [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130],
            array_map(function (Node $node) {
                return $node->position();
            }, $result)
        );

        $result = $nodeProvider->byQuery(
            NodeQuery::create()
                ->sort(NodeQuery::POSITION, NodeQuery::DESC)
        );

        $this->assertEquals(
            [130, 120, 110, 100, 90, 80, 70, 60, 50, 40, 30, 20, 10],
            array_map(function (Node $node) {
                return $node->position();
            }, $result)
        );
    }


    public function slicingProvider(): array
    {
        return [
            [null, null, [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130]],
            [0, 3, [10, 20, 30]],
            [3, 3, [40, 50, 60]],
            [null, 3, [10, 20, 30]],
            [3, null, [40, 50, 60, 70, 80, 90, 100, 110, 120, 130]],
        ];
    }

    /**
     * @test
     * @dataProvider slicingProvider
     */
    public function slicing(?int $start, ?int $length, array $expectedItems)
    {
        $nodeProvider = $this->createNodeProvider();

        $result = $nodeProvider->byQuery(
            NodeQuery::create()
                ->sort(NodeQuery::POSITION, NodeQuery::ASC)
                ->slice($start, $length)
        );

        $this->assertEquals(
            $expectedItems,
            array_map(function (Node $node) {
                return $node->position();
            }, $result)
        );
    }

    /**
     * @test
     */
    public function filter_by_root()
    {
        $nodeProvider = $this->createNodeProvider();

        $result = $nodeProvider->byQuery(
            NodeQuery::create()
                ->setRootOnly()
        );

        $this->assertEquals(
            [
                'code',
                'anagraphic',
                'items',
                'tags',
            ],
            array_map(function (Node $node) {
                return (string)$node->code();
            }, $result)
        );
    }

    /**
     * @test
     */
    public function filter_by_parent_id()
    {
        $nodeProvider = $this->createNodeProvider();

        $result = $nodeProvider->byQuery(
            NodeQuery::create()
                ->setParentId((string)$this->getNode('anagraphic')->id())
        );

        $this->assertEquals(
            [
                'name',
                'lastname',
                'active',
            ],
            array_map(function (Node $node) {
                return $node->code();
            }, $result)
        );
    }

    /**
     * @test
     */
    public function by_code()
    {
        $nodeProvider = $this->createNodeProvider();

        $node = $nodeProvider->byCode(new Code('name'));
        $this->assertEquals($this->getNode('name'), $node);
    }

    /**
     * @test
     */
    public function by_id()
    {
        $nodeProvider = $this->createNodeProvider();

        $node = $nodeProvider->byId($this->getNode('name')->id());
        $this->assertEquals($this->getNode('name'), $node);

        $this->assertNull($nodeProvider->byId(null));

    }
}
