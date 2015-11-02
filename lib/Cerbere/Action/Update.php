<?php

namespace Cerbere\Action;

use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;

/**
 * Class Update
 * @package Cerbere\Action
 */
class Update
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
     *
     */
    public function __construct()
    {

    }

    /**
     * @param int $status
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
     * @param Project $project
     * @param ReleaseHistory $releaseHistory
     */
    public function compare(Project $project, ReleaseHistory $releaseHistory)
    {
        // If the project status is marked as something bad, there's nothing else
        // to consider.
        if ($releaseHistory->getProjectStatus()) {
            switch ($releaseHistory->getProjectStatus()) {
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
            $project->setProjectStatus($releaseHistory->getProjectStatus());

            return;
        }

        // Figure out the target major version.
        $existing_major = $project->getExistingMajor();
        $supported_majors = array();
        if ($releaseHistory->getSupportedMajors()) {
            $supported_majors = explode(',', $releaseHistory->getSupportedMajors());
        } elseif ($releaseHistory->getDefaultMajor()) {
            // Older release history XML file without supported or recommended.
            $supported_majors[] = $releaseHistory->getDefaultMajor();
        }

        if (in_array($existing_major, $supported_majors)) {
            // Still supported, stay at the current major version.
            $target_major = $existing_major;
        } elseif ($releaseHistory->getRecommendedMajor()) {
            // Since 'recommended_major' is defined, we know this is the new XML
            // format. Therefore, we know the current release is unsupported since
            // its major version was not in the 'supported_majors' list. We should
            // find the best release from the recommended major version.
            $target_major = $releaseHistory->getRecommendedMajor();
            $project->setStatus(self::UPDATE_NOT_SUPPORTED);
        } elseif ($releaseHistory->getDefaultMajor()) {
            // Older release history XML file without recommended, so recommend
            // the currently defined "default_major" version.
            $target_major = $releaseHistory->getDefaultMajor();
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

        $release_patch_changed = '';
        $patch = '';

        // If the project is marked as UPDATE_FETCH_PENDING, it means that the
        // data we currently have (if any) is stale, and we've got a task queued
        // up to (re)fetch the data. In that case, we mark it as such, merge in
        // whatever data we have (e.g. project title and link), and move on.
        if ($releaseHistory->getFetchStatus() == self::UPDATE_FETCH_PENDING) {
            $project->setStatus(self::UPDATE_FETCH_PENDING);
            $project->setReason('No available update data');
            $project->setFetchStatus($releaseHistory->getFetchStatus());

            return;
        }

        // Defend ourselves from XML history files that contain no releases.
        if (!$releaseHistory->getReleases()) {
            $project->setStatus(self::UPDATE_UNKNOWN);
            $project->setReason('No available releases found');

            return;
        }

        foreach ($releaseHistory->getReleases() as $version => $release) {
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
                    $patch = $release->getVersionPatch();
                    $release_patch_changed = $release;
                }
                if (!$release->getVersionExtra() && $patch == $release->getVersionPatch()) {
                    $project->setRecommended($release_patch_changed->getVersion());
                    $project->setRelease($release_patch_changed->getVersion(), $release_patch_changed);
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
            if ($project->getDevVersion() && $releaseHistory->getRelease($project->getDevVersion())->getDate(
              ) > $releaseHistory->getRelease($project->getLatestVersion())->getDate()
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
                $latest = $releaseHistory->getRelease($project->getLatestDev());

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
}
