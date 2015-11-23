<?php

namespace Cerbere\Tests\Units\Versioning;

use Cerbere\Tests\AbstractTest;
use GitWrapper\GitWrapper;

class Git extends AbstractTest
{
    public function testConstruct()
    {
        $git = new \Cerbere\Versioning\Git();
        $this->object($git->getWrapper())->isInstanceOf('\GitWrapper\GitWrapper');
        $this->string($git->getCode())->isEqualTo('git');
        $this->variable($git->getWorkingDirectory())->isNull();
        $git->prepare('foo');
        $this->string($git->getWorkingDirectory())->contains(sys_get_temp_dir());
        $this->string($git->getWorkingDirectory())->contains('drush_tmp_');

        $old_wrapper = $git->getWrapper();
        $wrapper = new GitWrapper();
        $git->setWrapper($wrapper);
        $this->object($git->getWrapper())->isIdenticalTo($wrapper);
        $this->object($git->getWrapper())->isNotIdenticalTo($old_wrapper);
    }

    public function testProcess()
    {
        $git = new \Cerbere\Versioning\Git();
        $git->prepare('');
        $directory = $git->getWorkingDirectory();
        $files = glob($directory . DIRECTORY_SEPARATOR . '*');
        $this->array($files)->isEmpty();

        $options = array(
          'arguments' => array(
            'q',
            'branch' => 'master',
            'depth'  => 1,
          ),
        );
        $git->process('https://github.com/smalot/drush-cerbere.git', $directory, $options);
        chdir($directory);
        $files = glob('*');
        $this->array($files)->isNotEmpty();
        $this->array($files)->containsValues(array('composer.json', 'lib'));
    }
}
