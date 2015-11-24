<?php

/**
 * Drush Cerbere command line tools.
 * Copyright (C) 2015 - Sebastien Malot <sebastien@malot.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Cerbere\Tests\Units\Versioning;

use Cerbere\Tests\AbstractTest;
use GitWrapper\GitWrapper;

/**
 * Class Git
 * @package Cerbere\Tests\Units\Versioning
 */
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
