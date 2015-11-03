<?php

namespace Cerbere\Tests;

use mageekguy\atoum;

/**
 * Class AbstractTest
 * @package Cerbere
 */
abstract class AbstractTest extends atoum\test
{
    /**
     * @var array
     */
    protected $files = array();

    /**
     * @param string $data
     * @return string|false
     */
    protected function createFile($data)
    {
        $filename = $this->generateFilename();

        if (file_put_contents($filename, $data) !== false) {
            $this->files[] = $filename;

            register_shutdown_function(
              function() use ($filename) {
                  if (!unlink($filename)) {
                      ; // Nothing to do.
                  }
              }
            );

            return $filename;
        }

        return false;
    }

    /**
     * @param string $prefix
     * @return string
     */
    protected function generateFilename($prefix = 'atoum_')
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
}
