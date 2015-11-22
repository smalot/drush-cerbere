<?php

namespace Cerbere\Action;

use Cerbere\Model\Project;
use Cerbere\Model\Release;
use Cerbere\Model\ReleaseHistory;
use Doctrine\Common\Cache\CacheProvider;

/**
 * Class Update
 *
 * @package Cerbere\Action
 */
class Update implements ActionInterface
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * Update constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheProvider $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'update';
    }

    /**
     * @return void
     */
    public function prepare()
    {
    }

    /**
     * @param array $projects
     * @param array $options
     *
     * @return array
     */
    public function process(array $projects, $options = array())
    {
        $options += array('cache' => true, 'level' => 'all', 'flat' => false);
        $reports = array();

        $release_history = new ReleaseHistory($this->cache);

        /** @var Project $project */
        foreach ($projects as $project) {
            $release_history->prepare($project, $options['cache']);
            $release_history->compare($project);

            switch ($options['level']) {
                case 'security':
                    $level = ReleaseHistory::UPDATE_NOT_SECURE;
                    break;
                case 'unsupported':
                    $level = ReleaseHistory::UPDATE_NOT_SUPPORTED;
                    break;
                case 'update':
                    $level = ReleaseHistory::UPDATE_NOT_CURRENT;
                    break;
                default:
                    $level = ReleaseHistory::UPDATE_CURRENT;
            }

            if ($project->getStatus() <= $level) {
                $reports[$project->getProject()] = $this->generateReport($project, $release_history, $options['flat']);
            }
        }

        return $reports;
    }

    /**
     * @param \Cerbere\Model\Project $project
     * @param \Cerbere\Model\ReleaseHistory $release_history
     * @param boolean $flat
     * @return array
     */
    protected function generateReport(Project $project, ReleaseHistory $release_history, $flat = false)
    {
        $report = array(
          'project'      => $project->getProject(),
          'version'      => $project->getVersion(),
          'version_date' => $project->getDatestamp(),
          'recommended'  => null,
          'dev'          => null,
          'status'       => $project->getStatus(),
          'status_label' => ReleaseHistory::getStatusLabel($project->getStatus()),
          'reason'       => '',
        );

        if ($flat) {
            $report['recommended'] = $project->getRecommended();
            $report['dev'] = $project->getDevVersion();
        } else {
            if ($release = $release_history->getRelease($project->getRecommended())) {
                $report['recommended'] = $this->getReportFromRelease($release);
            }

            if ($release = $release_history->getRelease($project->getDevVersion())) {
                $report['dev'] = $this->getReportFromRelease($release);
            }
        }
        
        if ($reason = $project->getReason()) {
            $report['reason'] = $reason;
        }

        return $report;
    }

    /**
     * @param \Cerbere\Model\Release $release
     * @return array
     */
    protected function getReportFromRelease(Release $release)
    {
        return array(
          'version'       => $release->getVersion(),
          'datestamp'     => $release->getDatestamp(),
          'release_link'  => $release->getReleaseLink(),
          'download_link' => $release->getDownloadLink(),
          'filesize'      => $release->getFilesize(),
        );
    }
}
