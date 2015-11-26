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
use Cerbere\Model\Hacked\HackedProject;
use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Hacked
 *
 * @package Cerbere\Action
 */
class Hacked implements ActionInterface
{
    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return 'hacked';
    }

    /**
     * @param EventSubscriberInterface $listener
     */
    public function addLoggerListener(EventSubscriberInterface $listener)
    {
        $this->getDispatcher()->addSubscriber($listener);
    }

    /**
     * @inheritDoc
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {

    }

    /**
     * @inheritDoc
     */
    public function process(array $projects, $options = array())
    {
        if (empty($projects)) {
            return array();
        }

        $reports = array();
        $release_history = new ReleaseHistory();

        /** @var Project $project */
        foreach ($projects as $project) {
//            if ($project->getProject() != 'scald') continue;

            $event = new CerbereDoActionEvent($this, $project);
            $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_DO_ACTION, $event);

            if ($filename = $project->getFilename()) {
                $release_history->prepare($project);

                $current_dir = getcwd();
                // Change current directory to the module directory.
                chdir(dirname($filename));

                $hacked = new HackedProject($project);
                $result = $hacked->computeReport();

                $report = array(
                  'project' => $project->getProject(),
                  'version' => $project->getVersion(),
                  'version_date' => $project->getDatestamp(),
                  'status' => $result['status'],
                  'status_label' => HackedProject::getStatusLabel($result['status']),
                  'modified' => $result['counts']['different'],
                  'deleted' => $result['counts']['missing'],
                );

                $event = new CerbereReportActionEvent($this, $project, $report);
                $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_REPORT_ACTION, $event);
                $report = $event->getReport();

                $reports[] = $report;

                // Restore current directory.
                chdir($current_dir);
            }

            $event = new CerbereDoneActionEvent($this, $project);
            $this->getDispatcher()->dispatch(CerbereEvents::CERBERE_DONE_ACTION, $event);
        }

        return $reports;
    }
}
