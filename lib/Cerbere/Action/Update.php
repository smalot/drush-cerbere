<?php

namespace Cerbere\Action;

use Cerbere\Model\Config;
use Cerbere\Model\Part;
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
     *
     * @return array
     */
    public function process(Part $part)
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

            if ($project->getStatus() != ReleaseHistory::UPDATE_CURRENT) {
                $reason = $project->getReason();
            } else {
                $reason = '';
            }

            if ($project->getStatus() <= $level) {
                $reports[$project->getProject()] = array(
                  'project'      => $project->getProject(),
                  'project_name' => $project->getName(),
                  'version'      => $project->getVersion(),
                  'recommended'  => $project->getRecommended(),
                  'status'       => $project->getStatus(),
                  'status_label' => ReleaseHistory::getStatusLabel($project->getStatus()),
                  'reason'       => $reason,
                );
            }
        }

        return $reports;
    }
}
