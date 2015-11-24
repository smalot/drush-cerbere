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

use GitWrapper\GitWrapper;

/**
 * Class Git
 * @package Cerbere\Versioning
 */
class Git implements VersioningInterface
{
    /**
     * @var GitWrapper
     */
    protected $wrapper;

    /**
     * @var string
     */
    protected $workDirectory;

    /**
     * @param GitWrapper $wrapper
     */
    public function __construct(GitWrapper $wrapper = null)
    {
        if (null === $wrapper) {
            $wrapper = new GitWrapper();
        }

        $this->wrapper = $wrapper;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'git';
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
     * @return void
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
        $parameters = array();

        $options += array('arguments' => array());

        foreach ($options['arguments'] as $param => $value) {
            if (is_numeric($param)) {
                $parameters[$value] = true;
            } else {
                $parameters[$param] = $value;
            }
        }

        $this->wrapper->cloneRepository($source, $destination, $parameters);
    }

    /**
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * @param GitWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }
}
