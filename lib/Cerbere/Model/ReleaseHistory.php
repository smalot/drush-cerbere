<?php

namespace Cerbere\Model;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Class ReleaseHistory
 *
 * @package Cerbere\Action
 */
class ReleaseHistory
{
    /**
     * Project is missing security update(s).
     */
    const UPDATE_NOT_SECURE = 1;

    /**
     * Current release has been unpublished and is no longer available.
     */
    const UPDATE_REVOKED = 2;

    /**
     * Current release is no longer supported by the project maintainer.
     */
    const UPDATE_NOT_SUPPORTED = 3;

    /**
     * Project has a new release available, but it is not a security release.
     */
    const UPDATE_NOT_CURRENT = 4;

    /**
     * Project is up to date.
     */
    const UPDATE_CURRENT = 5;

    /**
     * Project's status cannot be checked.
     */
    const UPDATE_NOT_CHECKED = -1;

    /**
     * No available update data was found for project.
     */
    const UPDATE_UNKNOWN = -2;

    /**
     * There was a failure fetching available update data for this project.
     */
    const UPDATE_NOT_FETCHED = -3;

    /**
     * We need to (re)fetch available update data for this project.
     */
    const UPDATE_FETCH_PENDING = -4;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @param Project       $project
     * @param CacheProvider $cache
     * @param string        $url
     */
    public function __construct(Project $project, CacheProvider $cache = null, $url = null)
    {
        $this->project = $project;
        $this->cache   = $cache;
        $this->url     = $url;
    }

    /**
     * @param int $status
     *
     * @return string
     */
    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::UPDATE_NOT_SECURE:
                return 'Not secure';
            case self::UPDATE_REVOKED:
                return 'Revoked';
            case self::UPDATE_NOT_SUPPORTED:
                return 'Not supported';
            case self::UPDATE_NOT_CURRENT:
                return 'Not current';
            case self::UPDATE_CURRENT:
                return 'Update current';
            case self::UPDATE_NOT_CHECKED:
                return 'Not checked';
            case self::UPDATE_UNKNOWN:
                return 'Unknown';
            case self::UPDATE_NOT_FETCHED:
                return 'Not fetched';
            case self::UPDATE_FETCH_PENDING:
                return 'Fetch pending';
            default:
                return '';
        }
    }

    /**
     * @param bool|false $reset
     */
    public function prepare($reset = false)
    {
        $cid_parts = array(
          'release_history',
          $this->project->getProject(),
          $this->project->getCore(),
        );

        $cid  = implode(':', $cid_parts);
        $data = false;

        drush_print('   - ' . $this->project->getProject());

        if ($this->cache && !$reset) {
            $data = $this->cache->fetch($cid);
        }

        // If not in cache, load from remote.
        if ($data === false) {
            $url = $this->project->getStatusUrl() . '/' .
              $this->project->getProject() . '/' .
              $this->project->getCore();

            // Todo: prefer guzzle library.
            $content = file_get_contents($url);

            // If data, store into cache.
            if ($this->cache && ($data = $this->parseUpdateXml($content))) {
                $this->cache->save($cid, $data, 1800);
            }
        }

        // Hydrate release objects.
        if (isset($data['releases']) && is_array($data['releases'])) {
            foreach ($data['releases'] as $key => $value) {
                $data['releases'][$key] = new Release($value);
            }
        } else {
            $data['releases'] = array();
        }

        $this->data = $data;
    }

    /**
     * Parses the XML of the Drupal release history info files.
     *
     * @param string $raw_xml
     *   A raw XML string of available release data for a given project.
     *
     * @return array
     *   Array of parsed data about releases for a given project, or NULL if there
     *   was an error parsing the string.
     */
    protected function parseUpdateXml($raw_xml)
    {
        try {
            $xml = new \SimpleXMLElement($raw_xml);
        } catch (\Exception $e) {
            // SimpleXMLElement::__construct produces an E_WARNING error message for
            // each error found in the XML data and throws an exception if errors
            // were detected. Catch any exception and return failure (NULL).
            return array();
        }

        // If there is no valid project data, the XML is invalid, so return failure.
        if (!isset($xml->short_name)) {
            return array();
        }

        $data = array();
        foreach ($xml as $k => $v) {
            $data[$k] = (string) $v;
        }
        $data['releases'] = array();

        if (isset($xml->releases)) {
            foreach ($xml->releases->children() as $release) {
                $version                    = (string) $release->version;
                $data['releases'][$version] = array();
                foreach ($release->children() as $k => $v) {
                    $data['releases'][$version][$k] = (string) $v;
                }
                $data['releases'][$version]['terms'] = array();
                if ($release->terms) {
                    foreach ($release->terms->children() as $term) {
                        if (!isset($data['releases'][$version]['terms'][(string) $term->name])) {
                            $data['releases'][$version]['terms'][(string) $term->name] = array();
                        }
                        $data['releases'][$version]['terms'][(string) $term->name][] = (string) $term->value;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param Project $project
     */
    public function compare(Project $project)
    {
        // If the project status is marked as something bad, there's nothing else
        // to consider.
        if ($this->getProjectStatus()) {
            switch ($this->getProjectStatus()) {
                case 'insecure':
                    $project->setStatus(self::UPDATE_NOT_SECURE);
                    break;
                case 'unpublished':
                case 'revoked':
                    $project->setStatus(self::UPDATE_REVOKED);
                    break;
                case 'unsupported':
                    $project->setStatus(self::UPDATE_NOT_SUPPORTED);
                    break;
                case 'not-fetched':
                    $project->setStatus(self::UPDATE_NOT_FETCHED);
                    break;

                default:
                    // Assume anything else (e.g. 'published') is valid and we should
                    // perform the rest of the logic in this function.
                    break;
            }
        }

        if ($project->getStatus()) {
            // We already know the status for this project, so there's nothing else to
            // compute. Record the project status into $project_data and we're done.
            $project->setProjectStatus($this->getProjectStatus());

            return;
        }

        // Figure out the target major version.
        $existing_major   = $project->getExistingMajor();
        $supported_majors = array();
        if ($this->getSupportedMajors()) {
            $supported_majors = explode(',', $this->getSupportedMajors());
        } elseif ($this->getDefaultMajor()) {
            // Older release history XML file without supported or recommended.
            $supported_majors[] = $this->getDefaultMajor();
        }

        if (in_array($existing_major, $supported_majors)) {
            // Still supported, stay at the current major version.
            $target_major = $existing_major;
        } elseif ($this->getRecommendedMajor()) {
            // Since 'recommended_major' is defined, we know this is the new XML
            // format. Therefore, we know the current release is unsupported since
            // its major version was not in the 'supported_majors' list. We should
            // find the best release from the recommended major version.
            $target_major = $this->getRecommendedMajor();
            $project->setStatus(self::UPDATE_NOT_SUPPORTED);
        } elseif ($this->getDefaultMajor()) {
            // Older release history XML file without recommended, so recommend
            // the currently defined "default_major" version.
            $target_major = $this->getDefaultMajor();
        } else {
            // Malformed XML file? Stick with the current version.
            $target_major = $existing_major;
        }

        // Make sure we never tell the admin to downgrade. If we recommended an
        // earlier version than the one they're running, they'd face an
        // impossible data migration problem, since Drupal never supports a DB
        // downgrade path. In the unfortunate case that what they're running is
        // unsupported, and there's nothing newer for them to upgrade to, we
        // can't print out a "Recommended version", but just have to tell them
        // what they have is unsupported and let them figure it out.
        $target_major = max($existing_major, $target_major);

        $release_patch_changed = null;
        $patch                 = '';

        // If the project is marked as UPDATE_FETCH_PENDING, it means that the
        // data we currently have (if any) is stale, and we've got a task queued
        // up to (re)fetch the data. In that case, we mark it as such, merge in
        // whatever data we have (e.g. project title and link), and move on.
        if ($this->getFetchStatus() == self::UPDATE_FETCH_PENDING) {
            $project->setStatus(self::UPDATE_FETCH_PENDING);
            $project->setReason('No available update data');
            $project->setFetchStatus($this->getFetchStatus());

            return;
        }

        // Defend ourselves from XML history files that contain no releases.
        if (!$this->getReleases()) {
            $project->setStatus(self::UPDATE_UNKNOWN);
            $project->setReason('No available releases found');

            return;
        }

        foreach ($this->getReleases() as $version => $release) {
            // First, if this is the existing release, check a few conditions.
            if ($project->getExistingVersion() == $version) {
                if ($release->hasTerm('Release type') &&
                  in_array('Insecure', $release->getTerm('Release type'))
                ) {
                    $project->setStatus(self::UPDATE_NOT_SECURE);
                } elseif ($release->getStatus() == 'unpublished') {
                    $project->setStatus(self::UPDATE_REVOKED);
                } elseif ($release->hasTerm('Release type') &&
                  in_array('Unsupported', $release->getTerm('Release type'))
                ) {
                    $project->setStatus(self::UPDATE_NOT_SUPPORTED);
                }
            }

            // Otherwise, ignore unpublished, insecure, or unsupported releases.
            if ($release->getStatus() == 'unpublished' ||
              ($release->hasTerm('Release type') &&
                (in_array('Insecure', $release->getTerm('Release type')) ||
                  in_array('Unsupported', $release->getTerm('Release type'))))
            ) {
                continue;
            }

            // Look for the 'latest version' if we haven't found it yet. Latest is
            // defined as the most recent version for the target major version.
            if (!$project->getLatestVersion() && $release->getVersionMajor() == $target_major) {
                $project->setLatestVersion($version);
                $project->setRelease($version, $release);
            }

            // Look for the development snapshot release for this branch.
            if (!$project->getDevVersion()
              && $release->getVersionMajor() == $target_major
              && $release->getVersionExtra() == Project::INSTALL_TYPE_DEV
            ) {
                $project->setDevVersion($version);
                $project->setRelease($version, $release);
            }

            // Look for the 'recommended' version if we haven't found it yet (see
            // phpdoc at the top of this function for the definition).
            if (!$project->getRecommended()
              && $release->getVersionMajor() == $target_major
              && $release->getVersionPatch()
            ) {
                if ($patch != $release->getVersionPatch()) {
                    $patch                 = $release->getVersionPatch();
                    $release_patch_changed = $release;
                }
                if (!$release->getVersionExtra() && $patch == $release->getVersionPatch()) {
                    $project->setRecommended($release_patch_changed->getVersion());
                    if ($release_patch_changed instanceof Release) {
                        $project->setRelease($release_patch_changed->getVersion(), $release_patch_changed);
                    }
                }
            }

            // Stop searching once we hit the currently installed version.
            if ($project->getExistingVersion() == $version) {
                break;
            }

            // If we're running a dev snapshot and have a timestamp, stop
            // searching for security updates once we hit an official release
            // older than what we've got. Allow 100 seconds of leeway to handle
            // differences between the datestamp in the .info file and the
            // timestamp of the tarball itself (which are usually off by 1 or 2
            // seconds) so that we don't flag that as a new release.
            if ($project->getInstallType() == Project::INSTALL_TYPE_DEV) {
                if (!$project->getDatestamp()) {
                    // We don't have current timestamp info, so we can't know.
                    continue;
                } elseif ($release->getDate() && ($project->getDatestamp() + 100 > $release->getDate())) {
                    // We're newer than this, so we can skip it.
                    continue;
                }
            }

            // See if this release is a security update.
            if ($release->hasTerm('Release type') && in_array('Security update', $release->getTerm('Release type'))) {
                $project->addSecurityUpdate($release->getVersion(), $release);
            }
        }

        // If we were unable to find a recommended version, then make the latest
        // version the recommended version if possible.
        if (!$project->getRecommended() && $project->getLatestVersion()) {
            $project->setRecommended($project->getLatestVersion());
        }

        // Check to see if we need an update or not.
        if ($project->hasSecurityUpdates()) {
            // If we found security updates, that always trumps any other status.
            $project->setStatus(self::UPDATE_NOT_SECURE);
        }

        if ($project->getStatus()) {
            // If we already know the status, we're done.
            return;
        }

        // If we don't know what to recommend, there's nothing we can report.
        // Bail out early.
        if (!$project->getRecommended()) {
            $project->setStatus(self::UPDATE_UNKNOWN);
            $project->setReason('No available releases found');

            return;
        }

        // If we're running a dev snapshot, compare the date of the dev snapshot
        // with the latest official version, and record the absolute latest in
        // 'latest_dev' so we can correctly decide if there's a newer release
        // than our current snapshot.
        if ($project->getInstallType() == Project::INSTALL_TYPE_DEV) {
            if ($project->getDevVersion() && $this->getRelease($project->getDevVersion())->getDate(
              ) > $this->getRelease($project->getLatestVersion())->getDate()
            ) {
                $project->setLatestDev($project->getDevVersion());
            } else {
                $project->setLatestDev($project->getLatestVersion());
            }
        }

        // Figure out the status, based on what we've seen and the install type.
        switch ($project->getInstallType()) {
            case Project::INSTALL_TYPE_OFFICIAL:
                if ($project->getExistingVersion() == $project->getRecommended() ||
                  $project->getExistingVersion() == $project->getLatestVersion()
                ) {
                    $project->setStatus(self::UPDATE_CURRENT);
                } else {
                    $project->setStatus(self::UPDATE_NOT_CURRENT);
                }
                break;

            case Project::INSTALL_TYPE_DEV:
                $latest = $this->getRelease($project->getLatestDev());

                if (!$project->getDatestamp()) {
                    $project->setStatus(self::UPDATE_NOT_CHECKED);
                    $project->setReason('Unknown release date');
                } elseif (($project->getDatestamp() + 100 > $latest->getDate())) {
                    $project->setStatus(self::UPDATE_CURRENT);
                } else {
                    $project->setStatus(self::UPDATE_NOT_CURRENT);
                }
                break;

            default:
                $project->setStatus(self::UPDATE_UNKNOWN);
                $project->setReason('Invalid info');
        }
    }

    /**
     * @return string
     */
    public function getProjectStatus()
    {
        return $this->data['project_status'];
    }

    /**
     * @return string
     */
    public function getSupportedMajors()
    {
        return $this->data['supported_majors'];
    }

    /**
     * @return int
     */
    public function getDefaultMajor()
    {
        return $this->data['default_major'];
    }

    /**
     * @return int
     */
    public function getRecommendedMajor()
    {
        return $this->data['recommended_major'];
    }

    /**
     * @return mixed
     */
    public function getFetchStatus()
    {
        return isset($this->data['fetch_status']) ? $this->data['fetch_status'] : 0;
    }

    /**
     * @return Release[]
     */
    public function getReleases()
    {
        return $this->data['releases'];
    }

    /**
     * @param string $version
     *
     * @return Release
     */
    public function getRelease($version)
    {
        return $this->data['releases'][$version];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Release
     */
    public function getLastRelease()
    {
        $release = reset($this->data['releases']);

        return $release;
    }

    /**
     * @return \Cerbere\Model\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Cerbere\Model\Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->data['short_name'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->data['type'];
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->data['api_version'];
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->data['link'];
    }

    /**
     * @return string
     */
    public function getTerms()
    {
        return trim($this->data['terms']);
    }
}
