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

namespace Cerbere\Parser;

use Cerbere\Model\Project;
use Composer\Json\JsonFile;
use Composer\Package\Locker;
use Composer\Repository\RepositoryManager;
use ComposerLockParser\Package;

/**
 * Class Composer
 * @package Cerbere\Parser
 */
class Composer extends Ini
{
    /**
     * @var Project[]
     */
    protected $projects;

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
        return 'composer';
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param string $content
     * @param string $filename
     *
     * @throws \Exception
     */
    public function processContent($content, $filename = null)
    {
        throw new \Exception('Not supported');
    }

    /**
     * @param string $filename
     */
    public function processFile($filename)
    {
        $composerInfo = new \ComposerLockParser\ComposerInfo($filename);
        $composerInfo->parse();

        $this->projects = array();
        // Default value if not autodetected.
        $core = '8.x';

        /** @var Package $package */
        foreach ($composerInfo->getPackages() as $package) {
            if (strpos($package->getName(), 'drupal/') === 0 && $source = $package->getSource()) {
                $name = $this->getName($package);
                $version = $this->getVersion($package, $core);
                $core = substr($version, 0, 1) . '.x';
                $project = new Project($name, $core, $version, $package->getTime());
                $this->projects[] = $project;
            }
        }
    }

    /**
     * @parser string $filename
     *
     * @return bool
     */
    public function supportedFile($filename)
    {
        return preg_match('/\.lock/i', $filename) > 0;
    }

    /**
     * @param Package $package
     *
     * @return string
     */
    protected function getName(Package $package)
    {
        if ($package->getName() == 'drupal/core') {
            return 'drupal';
        } else {
            return preg_replace('/^drupal\//', '', $package->getName());
        }
    }

    /**
     * @param Package $package
     * @param string $core
     *
     * @return string
     */
    protected function getVersion(Package $package, $core)
    {
        $source = $package->getSource();

        if (preg_match('/^[0-9]+\.x\-/', $source['reference'])) {
            return $source['reference'];
        } elseif ($this->getName($package) == 'drupal') {
            return $package->getVersion();
        } else {
            $version = 'N/A';

            if (preg_match('/^([0-9]+\.[0-9]+)\.[0-9]+(.*)/', $package->getVersion(), $match)) {
                $version = $match[1] . $match[2];

                return $core . '-' . $version;
            }

            return $version;
        }
    }
}
