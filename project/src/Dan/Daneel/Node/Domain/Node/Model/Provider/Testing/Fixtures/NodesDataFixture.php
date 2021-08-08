<?php
declare(strict_types=1);

namespace Dan\Daneel\Node\Domain\Node\Model\Provider\Testing\Fixtures;

use Dan\FixtureHandler\Fixture\AbstractFixture;
use Symfony\Component\Yaml\Yaml;

class NodesDataFixture extends AbstractFixture
{
    private $position;

    public function load(): void
    {
        $filename = __DIR__ . '/data/nodes.yml';
        $data = Yaml::parse(file_get_contents($filename));
        $this->position = 0;
        $this->setRef('data.nodes', $this->getNodesData($data['nodes']));
    }

    private function getNodesData(array $data, ?string $parent = null): array
    {
        $result = [];
        foreach ($data as $code => $itemData) {
            $this->position += 10;
            $itemData = array_replace([
                'code' => $code,
                'label' => null,
                'parent' => null,
                'position' => $this->position,
            ], $itemData);

            $children = [];
            if (isset($itemData['children'])) {
                $children = $this->getNodesData($itemData['children'], $code);
                unset($itemData['children']);
            }

            if ($parent) {
                $itemData['parent'] = $parent;
            }
            $result[] = $itemData;
            foreach ($children as $childData) {
                $result[] = $childData;
            }
        }
        return $result;
    }
}