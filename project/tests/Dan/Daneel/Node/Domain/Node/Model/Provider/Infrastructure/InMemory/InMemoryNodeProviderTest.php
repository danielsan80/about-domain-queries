<?php

namespace Tests\Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure\InMemory;

use Dan\Daneel\Node\Domain\Node\Model\Provider\Infrastructure\InMemory\InMemoryNodeProvider;
use Dan\Daneel\Node\Domain\Node\Model\Provider\NodeProvider;
use Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\NodeProviderTestCase;

class InMemoryNodeProviderTest extends NodeProviderTestCase
{
    protected function createNodeProvider(): NodeProvider
    {
        return new InMemoryNodeProvider($this->getNodes());
    }
}
