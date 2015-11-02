<?php

namespace Cerbere;

use mageekguy\atoum;

/**
 * Class Test
 * @package Cerbere
 */
abstract class Test extends atoum\test
{
    /**
     * @var array
     */
    protected $files = array();

    /**
     * @param string $data
     * @return bool|string
     */
    protected function createFile($data)
    {
        $filename = $this->generateFilename();

        if (file_put_contents($filename, $data) !== false) {
            $this->files[] = $filename;

            register_shutdown_function(
              function () use ($filename) {
                  @unlink($filename);
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
