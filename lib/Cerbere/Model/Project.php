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

namespace Cerbere\Model;

/**
 * Class Project
 *
 * @package Cerbere\Model
 */
class Project
{
    /**
     * URL to check for updates, if a given project doesn't define its own.
     */
    const UPDATE_DEFAULT_URL = 'http://updates.drupal.org/release-history';

    /**
     *
     */
    const INSTALL_TYPE_OFFICIAL = 'official';

    /**
     *
     */
    const INSTALL_TYPE_DEV = 'dev';

    /**
     *
     */
    const INSTALL_TYPE_UNKNOWN = 'unknown';

    /**
     *
     */
    const TYPE_PROJECT_DISTRIBUTION = 'project_distribution';

    /**
     *
     */
    const TYPE_PROJECT_CORE = 'project_core';

    /**
     *
     */
    const TYPE_PROJECT_MODULE = 'project_module';

    /**
     *
     */
    const TYPE_PROJECT_THEME = 'project_theme';

    /**
     *
     */
    const TYPE_UNKNOWN = 'unknown';

    /**
     * @var
     */
    protected $project;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $core;

    /**
     * @var
     */
    protected $version;

    /**
     * @var
     */
    protected $status_url;

    /**
     * @var
     */
    protected $install_type;

    /**
     * @var
     */
    protected $existing_version;

    /**
     * @var
     */
    protected $existing_major;

    /**
     * @var array
     */
    protected $data;

    // Calculated properties.

    /**
     * @var
     */
    protected $status;

    /**
     * @var
     */
    protected $project_status;

    /**
     * @var string
     */
    protected $project_type;

    /**
     * @var
     */
    protected $reason;

    /**
     * @var
     */
    protected $fetch_status;

    /**
     * @var
     */
    protected $latest_version;

    /**
     * @var
     */
    protected $latest_dev;

    /**
     * @var
     */
    protected $dev_version;

    /**
     * @var
     */
    protected $recommended;

    /**
     * @var
     */
    protected $datestamp;

    /**
     * @var array
     */
    protected $releases;

    /**
     * @var array
     */
    protected $security_updates;

    /**
     * @var array
     */
    protected $also_available;

    /**
     * @param string $project
     * @param string $core
     * @param string $version
     * @param \DateTime|int|null $date
     */
    public function __construct($project, $core, $version, $date = null)
    {
        $this->project = $project;
        $this->name    = $project;
        $this->core    = $core;
        $this->version = $version;

        if ($date instanceof \DateTime) {
            $this->datestamp = $date->getTimestamp();
        } elseif (is_int($date)) {
            $this->datestamp = $date;
        }

        $this->project_type = self::TYPE_UNKNOWN;

        $this->releases         = array();
        $this->security_updates = array();
        $this->also_available   = array();

        $this->init();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        $this->init();
    }

    /**
     * @return string
     */
    public function getWorkingDirectory()
    {
        if ($this->getProject() == 'drupal' && $this->getCore() == '8.x') {
            $path = str_replace('\\', '/', dirname($this->getFilename()));

            // Todo: Optimize.
            if (($position = strpos($path, '/core/modules/')) !== false) {
                return substr($path, 0, $position);
            }
            if (($position = strpos($path, '/core/profiles/')) !== false) {
                return substr($path, 0, $position);
            }
            if (($position = strpos($path, '/core/themes/')) !== false) {
                return substr($path, 0, $position);
            }
            if (($position = strpos($path, '/core/tests/')) !== false) {
                return substr($path, 0, $position);
            }
        } elseif ($this->getProject() == 'drupal' && $this->getCore() == '7.x') {
            return dirname($this->getFilename()) . '/../..';
        }

        return dirname($this->getFilename());
    }

    /**
     *
     */
    protected function init()
    {
        // Patch project if Drupal detected.
        if (isset($this->data['package']) && strtolower($this->data['package']) == 'core') {
            $this->name = 'Drupal';

            $this->data = array(
              'name' => $this->name,
              'description' => '',
              'package' => $this->data['package'],
              'core' => $this->data['core'],
              'version' => $this->data['version'],
              'files' => array(),
              'configure' => '',
              'project' => 'drupal',
              'datestamp' => $this->data['datestamp'],
            );
        }

        $this->status_url = self::UPDATE_DEFAULT_URL;

        // Assume an official release until we see otherwise.
        $this->install_type     = self::INSTALL_TYPE_OFFICIAL;
        $this->existing_version = $this->version;

        if (isset($this->version)) {
            // Check for development snapshots
            if (preg_match('/(dev|HEAD)/', $this->version)) {
                $this->install_type = self::INSTALL_TYPE_DEV;
            }

            // Figure out what the currently installed major version is. We need
            // to handle both contribution (e.g. "5.x-1.3", major = 1) and core
            // (e.g. "5.1", major = 5) version strings.
            $matches = array();
            if (preg_match('/^(\d+\.x-)?(\d+)\..*$/', $this->version, $matches)) {
                $this->existing_major = $matches[2];
            } else {
                // This would only happen for version strings that don't follow the
                // drupal.org convention. We let contribs define "major" in their
                // .info in this case, and only if that's missing would we hit this.
                $this->existing_major = -1;
            }
        } else {
            // No version info available at all.
            $this->install_type     = self::INSTALL_TYPE_UNKNOWN;
            $this->existing_version = 'Unknown';
            $this->existing_major   = -1;
        }
    }

    /**
     * @param string  $version
     * @param Release $release
     */
    public function addSecurityUpdate($version, $release)
    {
        $this->security_updates[$version] = $release;
    }

    /**
     * @param string $version_major
     * @param string $version
     */
    public function addAlsoAvailable($version_major, $version)
    {
        $this->also_available[$version_major] = $version;
    }

    /**
     * @return array
     */
    public function getAlsoAvailable()
    {
        return $this->also_available;
    }

    /**
     * @param string $version_major
     * @return bool
     */
    public function hasAlsoAvailable($version_major)
    {
        return isset($this->also_available[$version_major]);
    }

    /**
     * @return mixed
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @return mixed
     */
    public function getDatestamp()
    {
        return $this->datestamp;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getProjectType()
    {
        return $this->project_type;
    }

    /**
     * @param string $type
     */
    public function setProjectType($type)
    {
        $this->project_type = $type;
    }

    /**
     * @return mixed
     */
    public function getDevVersion()
    {
        return $this->dev_version;
    }

    /**
     * @param $dev_version
     */
    public function setDevVersion($dev_version)
    {
        $this->dev_version = $dev_version;
    }

    /**
     * @return string
     */
    public function getExistingMajor()
    {
        return $this->existing_major;
    }

    /**
     * @return string
     */
    public function getExistingVersion()
    {
        return $this->existing_version;
    }

    /**
     * @return integer
     */
    public function getFetchStatus()
    {
        return $this->fetch_status;
    }

    /**
     * @param $fetch_status
     */
    public function setFetchStatus($fetch_status)
    {
        $this->fetch_status = $fetch_status;
    }

    /**
     * @return string
     */
    public function getInstallType()
    {
        return $this->install_type;
    }

    /**
     * @return mixed
     */
    public function getLatestDev()
    {
        return $this->latest_dev;
    }

    /**
     * @param $latest_dev
     */
    public function setLatestDev($latest_dev)
    {
        $this->latest_dev = $latest_dev;
    }

    /**
     * @return mixed
     */
    public function getLatestVersion()
    {
        return $this->latest_version;
    }

    /**
     * @param $latest_version
     */
    public function setLatestVersion($latest_version)
    {
        $this->latest_version = $latest_version;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return int
     */
    public function getProjectStatus()
    {
        return $this->project_status;
    }

    /**
     * @param int $project_status
     */
    public function setProjectStatus($project_status)
    {
        $this->project_status = $project_status;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getRecommended()
    {
        return $this->recommended;
    }

    /**
     * @param $recommended
     */
    public function setRecommended($recommended)
    {
        $this->recommended = $recommended;
    }

    /**
     * @return Release[]
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * @param Release[] $releases
     */
    public function setReleases($releases)
    {
        $this->releases = $releases;
    }

    /**
     * @param string $release
     *
     * @return Release|false
     */
    public function getRelease($release)
    {
        if (isset($this->releases[$release])) {
            return $this->releases[$release];
        }

        return false;
    }

    /**
     * @return Release[]
     */
    public function getSecurityUpdates()
    {
        return $this->security_updates;
    }

    /**
     * @param Release[] $security_updates
     */
    public function setSecurityUpdates($security_updates)
    {
        $this->security_updates = $security_updates;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->status_url;
    }

    /**
     * @param string $status_url
     */
    public function setStatusUrl($status_url)
    {
        $this->status_url = $status_url;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function hasSecurityUpdates()
    {
        return count($this->security_updates) > 0;
    }

    /**
     * @param array $data
     */
    public function setDetails($data)
    {
        $this->data = $data;

        foreach (array('name', 'core', 'version', 'datestamp') as $property) {
            if (isset($data[$property])) {
                $this->$property = $data[$property];
            }
        }

        $this->init();
    }

    /**
     * @param string  $version
     * @param Release $release
     */
    public function setRelease($version, Release $release)
    {
        $this->releases[$version] = $release;
    }
}
