<?php

namespace Dan\Daneel\Node\Domain\Node\Model\Provider\Testing;

class TestUuid
{
    static public function create(int $i): string
    {
        return '00000000-0000-4000-8000-'.str_pad($i,12,'0',STR_PAD_LEFT);
    }

}