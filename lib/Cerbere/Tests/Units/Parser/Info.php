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

namespace Cerbere\Tests\Units\Parser;

use Cerbere\Tests\AbstractTest;

/**
 * Class Info
 * @package Cerbere\Tests\Units\Parser
 */
class Info extends AbstractTest
{
    public function testGetCode()
    {
        $make = new \Cerbere\Parser\Info();
        $this->string($make->getCode())->isEqualTo('info');
    }

    public function testGetProject()
    {
        $filename = $this->createInfoFile();
        $this->string($filename)->contains('ato');

        $info = new \Cerbere\Parser\Info();
        $info->processFile($filename);
        $this->class($info);

        $project = $info->getProject();
        $this->string(get_class($project))->isEqualTo('Cerbere\Model\Project');
        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-3.11');
        $this->string($project->getName())->isEqualTo('Views');
        $this->string($project->getDatestamp())->isEqualTo('1430321048');

        $this->array($info->getProjects())->hasSize(1);
    }

    protected function createInfoFile()
    {
        $data = 'name = Views
description = Create customized lists and queries from your database.
package = Views
core = 7.x
php = 5.2

; Always available CSS
stylesheets[all][] = css/views.css

dependencies[] = ctools
; Handlers
files[] = handlers/views_handler_area.inc

; Information added by Drupal.org packaging script on 2015-04-29
version = "7.x-3.11"
core = "7.x"
project = "views"
datestamp = "1430321048"
';

        return $this->createFile($data);
    }

    public function testSupportedFile()
    {
        $info = new \Cerbere\Parser\Info();

        $this->boolean($info->supportedFile('foo.bar'))->isFalse();
        $this->boolean($info->supportedFile('foo.yml'))->isFalse();
        $this->boolean($info->supportedFile('foo.yaml'))->isFalse();
        $this->boolean($info->supportedFile('foo.maker'))->isFalse();
        $this->boolean($info->supportedFile('info/foo.yml'))->isFalse();
        $this->boolean($info->supportedFile('make/foo.yml'))->isFalse();

        $this->boolean($info->supportedFile('foo.info'))->isTrue();
        $this->boolean($info->supportedFile('foo.Info'))->isTrue();
        $this->boolean($info->supportedFile('foo.INFO'))->isTrue();
        $this->boolean($info->supportedFile('test/foo.INFO'))->isTrue();
    }
}
