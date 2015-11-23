<?php

namespace Cerbere\Tests\Units\Versioning;

use Cerbere\Tests\AbstractTest;
use SvnWrapper\SvnWrapper;

class Svn extends AbstractTest
{
    public function testConstruct()
    {
        $svn = new \Cerbere\Versioning\Svn();
        $this->string($svn->getCode())->isEqualTo('svn');
        $this->variable($svn->getWorkingDirectory())->isNull();
        $svn->prepare('foo');
        $this->string($svn->getWorkingDirectory())->contains(sys_get_temp_dir());
        $this->string($svn->getWorkingDirectory())->contains('drush_tmp_');
    }

    public function testBuildCommande()
    {
        $svn = new \Cerbere\Versioning\Svn();
        $options = array('arguments' => array('q', 'branch' => 'master'));
        $command = $svn->buildCommandLine('source foo', 'destination bar', $options);
        $command = str_replace('"', "'", $command);
        $this->string(trim($command))->isEqualTo("'/usr/bin/svn' checkout 'source foo' 'destination bar' '-q' --branch='master'");
    }
}
