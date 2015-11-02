<?php

namespace Cerbere\Tests\Units\Parser;

use Cerbere\Test;

class Make extends Test {
  protected function createMakeFile() {
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

  public function testGetCore() {
    $filename = $this->createMakeFile();
    $this->string($filename)->contains('atoum');

    $make = new \Cerbere\Parser\Make($filename);
    $this->class($make);

    $this->string($make->getCore())->isEqualTo('7.x');
  }

  public function testGetVersion() {
    $filename = $this->createMakeFile();
    $this->string($filename)->contains('atoum');

    $make = new \Cerbere\Parser\Make($filename);
    $this->class($make);

    $this->string($make->getApi())->isEqualTo('2');
  }

  public function testGetProjects() {
    $filename = $this->createMakeFile();
    $this->string($filename)->contains('atoum');

    $make = new \Cerbere\Parser\Make($filename);
    $this->class($make);

    $projects = $make->getProjects();
    $project_names = array (
      0 => 'drupal',
      1 => 'admin_menu',
      2 => 'module_filter',
      3 => 'addressfield',
      4 => 'addressfield_sub_premise',
      5 => 'addressfield_phone',
      6 => 'auto_nodetitle',
      7 => 'ctools',
      8 => 'color_field',
      9 => 'custom_breadcrumbs',
      10 => 'entity',
    );

    $this->array($projects)->hasSize(11)->keys->isEqualTo($project_names);
  }

  public function testGetLibraries() {
    $filename = $this->createMakeFile();
    $this->string($filename)->contains('atoum');

    $make = new \Cerbere\Parser\Make($filename);
    $this->class($make);

    $libraries = $make->getLibraries();
    $project_names = array (
      0 => 'predis',
      1 => 'bgrins-spectrum',
    );

    $this->array($libraries)->hasSize(2)->keys->isEqualTo($project_names);
  }
}
