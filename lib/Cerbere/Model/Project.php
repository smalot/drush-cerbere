<?php

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
     * @var
     */
    protected $project;

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
     * @param string $project
     * @param string $core
     * @param string $version
     */
    public function __construct($project, $core, $version)
    {
        $this->project = $project;
        $this->name = $project;
        $this->core = $core;
        $this->version = $version;

        $this->releases = array();
        $this->security_updates = array();

        $this->init();
    }

    /**
     *
     */
    protected function init()
    {
        $this->status_url = self::UPDATE_DEFAULT_URL;

        // Assume an official release until we see otherwise.
        $this->install_type = self::INSTALL_TYPE_OFFICIAL;
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
            $this->install_type = self::INSTALL_TYPE_UNKNOWN;
            $this->existing_version = 'Unknown';
            $this->existing_major = -1;
        }
    }

    /**
     * @param Release $release
     */
    public function addSecurityUpdate($version, $release)
    {
        $this->security_updates[$version] = $release;
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
     * @param string $version
     * @param Release $release
     */
    public function setRelease($version, Release $release)
    {
        $this->releases[$version] = $release;
    }
}
