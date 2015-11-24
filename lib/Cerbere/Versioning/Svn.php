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

namespace Cerbere\Versioning;

/**
 * Class Svn
 * @package Cerbere\Versioning
 */
class Svn implements VersioningInterface
{
    const SVN_BINARY_PATH = '/usr/bin/svn';

    /**
     * @var string
     */
    protected $workDirectory;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'svn';
    }

    /**
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workDirectory;
    }

    /**
     * @param string $source
     *
     * @return mixed
     */
    public function prepare($source)
    {
        $this->workDirectory = drush_tempdir();
    }

    /**
     * @param string $source
     * @param string $destination
     * @param array $options
     *
     * @return string
     */
    public function process($source, $destination, $options = array())
    {
        $command = $this->buildCommandLine($source, $destination, $options);

        if (!empty($options['debug'])) {
            drush_print('$> ' . $command);
        }

        passthru($command);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param array $options
     *
     * @return string
     */
    public function buildCommandLine($source, $destination, $options = array())
    {
        $options += array('svn' => self::SVN_BINARY_PATH, 'arguments' => array());

        $command = escapeshellarg($options['svn']) . ' ' .
          'checkout ' . escapeshellarg($source) . ' ' .
          escapeshellarg($destination) . ' ';

        foreach ($options['arguments'] as $param => $value) {
            if (is_numeric($param)) {
                $command .= escapeshellarg('-' . $value) . ' ';
            } else {
                $command .= '--' . $param . '=' . escapeshellarg($value) . ' ';
            }
        }

        return $command;
    }
}
