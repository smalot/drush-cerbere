<?php

namespace Cerbere;

use mageekguy\atoum;

abstract class Test extends atoum\test {
  protected $files = array();

  protected function createFile($data) {
    $filename = $this->generateFilename();

    if (file_put_contents($filename, $data) !== false) {
      $this->files[] = $filename;

      register_shutdown_function(function() use ($filename) {
        @unlink($filename);
      });

      return $filename;
    }

    return false;
  }

  protected function generateFilename($prefix = 'atoum_') {
    return tempnam(sys_get_temp_dir(), $prefix);
  }
}
