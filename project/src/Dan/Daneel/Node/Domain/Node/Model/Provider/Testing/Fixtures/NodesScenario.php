<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\Fixtures;

use Dan\FixtureHandler\Scenario\AbstractScenario;

class NodesScenario extends AbstractScenario
{

    public function load(): void
    {
        $this->addFixture(new NodesDataFixture());
        $this->addFixture(new NodesFixture());
    }
}
