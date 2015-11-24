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
use SvnWrapper\SvnWrapper;

/**
 * Class Svn
 * @package Cerbere\Tests\Units\Versioning
 */
class Svn extends AbstractTest
{
    public function testBuildCommande()
    {
        $svn = new \Cerbere\Versioning\Svn();
        $options = array('arguments' => array('q', 'branch' => 'master'));
        $command = $svn->buildCommandLine('source foo', 'destination bar', $options);
        $command = str_replace('"', "'", $command);
        $this->string(trim($command))->isEqualTo(
          "'/usr/bin/svn' checkout 'source foo' 'destination bar' '-q' --branch='master'"
        );
    }

    public function testConstruct()
    {
        $svn = new \Cerbere\Versioning\Svn();
        $this->string($svn->getCode())->isEqualTo('svn');
        $this->variable($svn->getWorkingDirectory())->isNull();
        $svn->prepare('foo');
        $this->string($svn->getWorkingDirectory())->contains(sys_get_temp_dir());
        $this->string($svn->getWorkingDirectory())->contains('drush_tmp_');
    }
}
