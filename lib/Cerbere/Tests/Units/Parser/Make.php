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
 * Class Make
 * @package Cerbere\Tests\Units\Parser
 */
class Make extends AbstractTest
{
    public function testGetCode()
    {
        $make = new \Cerbere\Parser\Make();
        $this->string($make->getCode())->isEqualTo('make');
    }

    public function testGetCore()
    {
        $filename = $this->createMakeFile();
        $this->string($filename)->contains('ato');

        $make = new \Cerbere\Parser\Make();
        $make->processFile($filename);
        $this->class($make);

        $this->string($make->getCore())->isEqualTo('7.x');
    }

    protected function createMakeFile()
    {
        $data = 'core = 7.x
api = 2
projects[drupal][version] = "7.38"

; Drush make allows a default sub directory for all contributed projects.
defaults[projects][subdir] = contrib

; ----------------------
; Modules
; ----------------------

; Admin
projects[admin_menu][version] = "3.0-rc5"
projects[module_filter][version] = "2.0"

; Contrib
projects[addressfield][version] = "1.1"
projects[addressfield_sub_premise][version] = "1.0-beta4"
projects[addressfield_phone][version] = "1.2"
projects[auto_nodetitle][version] = "1.0"
projects[ctools][version] = "1.7"
projects[color_field][version] = "2.0-beta1"
projects[custom_breadcrumbs][version] = "2.0-beta1"
projects[entity][version] = "1.6"
projects[entity][patch][] = "https://www.drupal.org/files/issues/entity-apply_langcode-2335885-1.patch"

; Libraries
libraries[predis][directory_name] = "predis"
libraries[predis][type] = "library"
libraries[predis][destination] = "libraries"
libraries[predis][download][type] = "get"
libraries[predis][download][url] = https://github.com/nrk/predis/archive/v1.0.zip

libraries[bgrins-spectrum][directory_name] = "bgrins-spectrum"
libraries[bgrins-spectrum][type] = "library"
libraries[bgrins-spectrum][destination] = "libraries"
libraries[bgrins-spectrum][download][type] = "get"
libraries[bgrins-spectrum][download][url] = https://github.com/bgrins/spectrum/archive/1.6.0.zip
';

        return $this->createFile($data);
    }

    public function testGetLibraries()
    {
        $filename = $this->createMakeFile();
        $this->string($filename)->contains('ato');

        $make = new \Cerbere\Parser\Make();
        $make->processFile($filename);
        $this->class($make);

        $libraries = $make->getLibraries();
        $project_names = array(
          0 => 'predis',
          1 => 'bgrins-spectrum',
        );

        $this->array($libraries)->hasSize(2)->keys->isEqualTo($project_names);
    }

    public function testGetProjects()
    {
        $filename = $this->createMakeFile();
        $this->string($filename)->contains('ato');

        $make = new \Cerbere\Parser\Make();
        $make->processFile($filename);
        $this->class($make);

        $projects = $make->getProjects();
        $project_names = array(
          0  => 'drupal',
          1  => 'admin_menu',
          2  => 'module_filter',
          3  => 'addressfield',
          4  => 'addressfield_sub_premise',
          5  => 'addressfield_phone',
          6  => 'auto_nodetitle',
          7  => 'ctools',
          8  => 'color_field',
          9  => 'custom_breadcrumbs',
          10 => 'entity',
        );

        $this->array($projects)->hasSize(11)->keys->isEqualTo($project_names);

        $this->boolean($make->hasProject('ctools'))->isTrue();
        $this->boolean($make->hasProject('views'))->isFalse();

        $project = $make->getProject('ctools');
        $this->string($project->getProject())->isEqualTo('ctools');
        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-1.7');

        $this->array($make->getProjects())->hasSize(11)->keys->isEqualTo($project_names);
    }

    public function testGetVersion()
    {
        $filename = $this->createMakeFile();
        $this->string($filename)->contains('ato');

        $make = new \Cerbere\Parser\Make();
        $make->processFile($filename);
        $this->class($make);

        $this->string($make->getApi())->isEqualTo('2');
    }

    public function testSupportedFile()
    {
        $make = new \Cerbere\Parser\Make();

        $this->boolean($make->supportedFile('foo.bar'))->isFalse();
        $this->boolean($make->supportedFile('foo.info'))->isFalse();
        $this->boolean($make->supportedFile('foo.yml'))->isFalse();
        $this->boolean($make->supportedFile('foo.yaml'))->isFalse();
        $this->boolean($make->supportedFile('foo.maker'))->isFalse();
        $this->boolean($make->supportedFile('info/foo.yml'))->isFalse();
        $this->boolean($make->supportedFile('make/foo.yml'))->isFalse();

        $this->boolean($make->supportedFile('foo.make'))->isTrue();
        $this->boolean($make->supportedFile('foo.Make'))->isTrue();
        $this->boolean($make->supportedFile('foo.MAKE'))->isTrue();
        $this->boolean($make->supportedFile('test/foo.MAKE'))->isTrue();
    }
}
