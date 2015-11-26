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

namespace Cerbere\Action;

use Cerbere\Event\CerbereDoActionEvent;
use Cerbere\Event\CerbereDoneActionEvent;
use Cerbere\Event\CerbereEvents;
use Cerbere\Event\CerbereReportActionEvent;
use Cerbere\Model\Project;
use Cerbere\Model\Release;
use Cerbere\Model\ReleaseHistory;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Update
 * @package Cerbere\Action
 */
class Update implements ActionInterface
{
    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Update constructor.
     */
    public function __construct()
    {

    }

    /**
     * @inheritDoc
     */
    public function addLoggerListener(EventSubscriberInterface $listener)
    {
        $this->getDispatcher()->addSubscriber($listener);
    }

    /**
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The Symfony event dispatcher object.
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return void
     */
    public function prepare()
    {
    }

    /**
     * @param Project[] $projects
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
            $event = new CerbereDoActionEvent($this, $project);
            $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_DO_ACTION, $event);

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
                $report = $this->generateReport($project, $release_history, $options['flat']);

                $event = new CerbereReportActionEvent($this, $project, $report, $options);
                $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_REPORT_ACTION, $event);
                $report = $event->getReport();

                $reports[$project->getProject()] = $report;
            }

            $event = new CerbereDoneActionEvent($this, $project);
            $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_DONE_ACTION, $event);
        }

        return $reports;
    }

    /**
     * @param Project $project
     * @param ReleaseHistory $release_history
     * @param boolean $flat
     * @return array
     */
    protected function generateReport(Project $project, ReleaseHistory $release_history, $flat = false)
    {
        $report = array(
          'project'        => $project->getProject(),
          'type'           => $project->getProjectType(),
          'version'        => $project->getVersion(),
          'version_date'   => $project->getDatestamp(),
          'recommended'    => null,
          'dev'            => null,
          'also_available' => array(),
          'status'         => $project->getStatus(),
          'status_label'   => ReleaseHistory::getStatusLabel($project->getStatus()),
          'reason'         => '',
        );

        if ($flat) {
            $report['recommended'] = $project->getRecommended();
            $report['dev'] = $project->getDevVersion();
            $report['also_available'] = implode(',', $project->getAlsoAvailable());
        } else {
            if ($release = $release_history->getRelease($project->getRecommended())) {
                $report['recommended'] = $this->getReportFromRelease($release);
            }

            if ($release = $release_history->getRelease($project->getDevVersion())) {
                $report['dev'] = $this->getReportFromRelease($release);
            }

            foreach ($project->getAlsoAvailable() as $version) {
                if ($release = $project->getRelease($version)) {
                    $report['also_available'][] = $this->getReportFromRelease($release);
                }
            }
        }

        if ($reason = $project->getReason()) {
            $report['reason'] = $reason;
        }

        return $report;
    }

    /**
     * @param Release $release
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
    public function setCache(CacheProvider $cache)
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
}
