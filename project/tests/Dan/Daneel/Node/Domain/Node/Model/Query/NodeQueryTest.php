<?php

namespace Tests\Dan\Daneel\Node\Domain\Node\Model\Query;

use Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\TestUuid;
use Dan\Daneel\Node\Domain\Node\Model\Query\NodeQuery;
use PHPUnit\Framework\TestCase;

class NodeQueryTest extends TestCase
{

    /**
     * @test
     */
    public function you_can_create_an_empty_query()
    {
        $query = NodeQuery::create();
        $this->assertEquals([], $query->toArray());
    }

    /**
     * @test
     */
    public function you_can_create_a_query_using_the_fluent_interface()
    {
        $query = NodeQuery::create();
        $query = $query
            ->slice(0,10)
            ->sort('position')
            ->setRootOnly();

        $this->assertEquals([
            'start' => 0,
            'length' => 10,
            'sort_key' => 'position',
            'root' => true
        ], $query->toArray());
    }

    /**
     * @test
     */
    public function query_is_immutable()
    {
        $query1 = NodeQuery::create();
        $query2 = $query1->slice(0,10);
        $query3 = $query2->removeStart();
        $this->assertEquals([], $query1->toArray());
        $this->assertEquals([
            'start' => 0,
            'length' => 10,
        ], $query2->toArray());
        $this->assertEquals([
            'length' => 10,
        ], $query3->toArray());
    }




    public function slicingQueryProvider(): array
    {
        return [
            [null, null, []],
            [0, null, ['start' => 0]],
            [0, 5, ['start' => 0, 'length' => 5]],
            [null, 5, ['length' => 5]],
        ];
    }

    /**
     * @test
     * @dataProvider slicingQueryProvider
     */
    public function you_can_create_a_slicing_query(?int $start, ?int $length, array $result)
    {
        $query = NodeQuery::create(['start' => $start, 'length' => $length]);
        $this->assertEquals($result, $query->toArray());
    }

    public function sortingQueryProvider(): array
    {
        return [
            [null, null, []],
            ['position', null, ['sort_key' => 'position']],
            ['position', 'asc', ['sort_key' => 'position', 'sort_direction' => 'asc']],
            ['position', 'desc', ['sort_key' => 'position', 'sort_direction' => 'desc']],
            [null, 'desc', []],
        ];
    }

    /**
     * @test
     * @dataProvider sortingQueryProvider
     */
    public function you_can_create_a_sorting_query(?string $sortKey, ?string $sortDirection, array $result)
    {
        $query = NodeQuery::create(['sort_key' => $sortKey, 'sort_direction' => $sortDirection]);
        $this->assertEquals($result, $query->toArray());
    }

    /**
     * @test
     */
    public function you_can_filter_on_root()
    {
        $query = NodeQuery::create(['root' => true]);
        $this->assertEquals(['root' => true], $query->toArray());
    }

    /**
     * @test
     */
    public function you_can_filter_on_a_parent_id()
    {
        $query = NodeQuery::create(['parent_id' => TestUuid::create(1)]);
        $this->assertEquals(['parent_id' => TestUuid::create(1)], $query->toArray());
    }

    /**
     * @test
     */
    public function you_can_combine_slicing_sorting_and_filtering()
    {
        $query = NodeQuery::create([
            'start' => 0,
            'length' => 10,
            'sort_key' => 'position',
            'root' => true
        ]);
        $this->assertEquals([
            'start' => 0,
            'length' => 10,
            'sort_key' => 'position',
            'root' => true
        ], $query->toArray());
    }

    /**
     * @test
     */
    public function you_cannot_combine_root_and_parent_id_filters()
    {
        $this->expectExceptionMessage('"parent_id" is not compatible with "root"');
        $query = NodeQuery::create()
            ->setRootOnly()
            ->setParentId(TestUuid::create(1));
    }

    /**
     * @test
     */
    public function you_cannot_combine_parent_id_and_root_filters()
    {
        $this->expectExceptionMessage('"root" is not compatible with "parent_id"');
        $query = NodeQuery::create()
            ->setParentId(TestUuid::create(1))
            ->setRootOnly();
    }


    /**
     * @test
     */
    public function you_can_remove_parentId_and_add_root()
    {
        $query = NodeQuery::create()
            ->slice(0,10)
            ->setParentId(TestUuid::create(1))
            ->removeParentId()
            ->setRootOnly();
        $this->assertEquals([
            'start' => 0,
            'length' => 10,
            'root' => true
        ], $query->toArray());
    }


    /**
     * @test
     */
    public function you_cannot_use_unmanaged_sort_keys()
    {
        $this->expectExceptionMessage('Sort key "date" is unknown. Available ones are [position, label]');
        $query = NodeQuery::create()
            ->sort('date','desc');
    }

    /**
     * @test
     */
    public function you_cannot_use_unmanaged_sort_directions()
    {
        $this->expectExceptionMessage('Sort direction "random" is unknown. Available ones are [asc, desc]');
        $query = NodeQuery::create()
            ->sort('position','random');
    }
}
