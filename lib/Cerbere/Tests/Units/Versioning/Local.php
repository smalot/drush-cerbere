<?php

namespace Cerbere\Tests\Units\Versioning;

use Cerbere\Tests\AbstractTest;

class Local extends AbstractTest
{
    public function testConstruct()
    {
        $local = new \Cerbere\Versioning\Local();
        $this->string($local->getCode())->isEqualTo('local');
        $this->variable($local->getWorkingDirectory())->isNull();
        $local->prepare('foo');
        $this->string($local->getWorkingDirectory())->isEqualTo('foo');
        $local->process('bar', 'foo', array());
    }
}
