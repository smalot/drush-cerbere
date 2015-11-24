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
     *
     * @return string|false
     */
    protected function createFile($data)
    {
        $filename = $this->generateFilename();

        if (file_put_contents($filename, $data) !== false) {
            $this->files[] = $filename;

            register_shutdown_function(
              function () use ($filename) {
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
     *
     * @return string
     */
    protected function generateFilename($prefix = 'atoum_')
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
}
