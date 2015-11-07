<?php

namespace Cerbere\Action;

use Cerbere\Model\Config;
use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;

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
     * @param Config $config
     *
     * @return void
     */
    public function prepare(Config $config)
    {
        $this->config = $config;
        $this->cache  = new FilesystemCache(sys_get_temp_dir());
    }

    /**
     * @param Project $project
     *
     * @return void
     */
    public function process(Project $project)
    {
        $cache_reset     = empty($this->config['cache']);
        $release_history = new ReleaseHistory($project, $this->cache);
        $release_history->prepare($cache_reset);
        $release_history->compare($project);

        $level = isset($this->config['level']) ? $this->config['level'] : 'all';
        if ($level == 'security') {
            $level = ReleaseHistory::UPDATE_NOT_SECURE;
        } elseif ($level == 'update') {
            $level = ReleaseHistory::UPDATE_NOT_CURRENT;
        } else {
            $level = ReleaseHistory::UPDATE_CURRENT;
        }

        if ($project->getStatus() <= $level) {
            $line = str_pad($release_history->getShortName(), 45, ' ', STR_PAD_RIGHT);
            $line .= str_pad($project->getVersion(), 20, ' ', STR_PAD_RIGHT);
            $line .= str_pad($project->getRecommended(), 20, ' ', STR_PAD_RIGHT);

            if ($project->getStatus() != ReleaseHistory::UPDATE_CURRENT) {
                $line .= ReleaseHistory::getStatusLabel($project->getStatus());
                if ($reason = $project->getReason()) {
                    $line .= ' (' . $reason . ')';
                }
            }

            drush_print($line);
        }
    }
}
