<?php

namespace Cerbere\Action;

use Cerbere\Model\Config;
use Cerbere\Model\Part;
use Cerbere\Model\Project;
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
     * @var Config
     */
    protected $config;

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
     * @return string
     */
    public function getCode()
    {
        return 'update';
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
     * @param array $config
     *
     * @return void
     */
    public function prepare($config)
    {
        $this->config = $config;
    }

    /**
     * @param Part $part
     * @param boolean $flat
     *
     * @return array
     */
    public function process(Part $part, $flat = false)
    {
        $reports     = array();
        $cache_reset = empty($this->config['cache']) && isset($this->config['cache']);

        foreach ($part->getProjects() as $project) {
            $release_history = new ReleaseHistory($project, $this->cache);
            $release_history->prepare($cache_reset);
            $release_history->compare($project);

            $level = isset($this->config['level']) ? $this->config['level'] : 'all';

            if ($level == 'security') {
                $level = ReleaseHistory::UPDATE_NOT_SECURE;
            } elseif ($level == 'unsupported') {
                $level = ReleaseHistory::UPDATE_NOT_SUPPORTED;
            } elseif ($level == 'update') {
                $level = ReleaseHistory::UPDATE_NOT_CURRENT;
            } else {
                $level = ReleaseHistory::UPDATE_CURRENT;
            }

            if ($project->getStatus() <= $level) {
                $reports[$project->getProject()] = $this->generateReport($project, $release_history, $flat);
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
    protected function generateReport(Project $project, ReleaseHistory $release_history, $flat = false) {
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
                $report['recommended'] = array(
                  'version'       => $release->getVersion(),
                  'datestamp'     => $release->getDatestamp(),
                  'release_link'  => $release->getReleaseLink(),
                  'download_link' => $release->getDownloadLink(),
                  'filesize'      => $release->getFilesize(),
                );
            }

            if ($release = $release_history->getRelease($project->getDevVersion())) {
                $report['dev'] = array(
                  'version'       => $release->getVersion(),
                  'datestamp'     => $release->getDatestamp(),
                  'release_link'  => $release->getReleaseLink(),
                  'download_link' => $release->getDownloadLink(),
                  'filesize'      => $release->getFilesize(),
                );
            }
        }


        if ($reason = $project->getReason()) {
            $report['reason'] = $reason;
        }

        return $report;
    }
}
