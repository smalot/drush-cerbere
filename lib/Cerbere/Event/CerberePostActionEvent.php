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

namespace Cerbere\Event;

use Cerbere\Action\ActionInterface;
use Cerbere\Cerbere;
use Cerbere\Model\Job;
use Cerbere\Model\Project;

/**
 * Class CerberePostActionEvent
 * @package Cerbere\Event
 */
class CerberePostActionEvent extends CerbereEvent
{
    /**
     * @var Cerbere
     */
    protected $cerbere;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @var Project[]
     */
    protected $projects;

    /**
     * @param Cerbere $cerbere
     * @param Job $job
     * @param ActionInterface $action
     * @param Project[] $projects
     */
    public function __construct(Cerbere $cerbere, Job $job, ActionInterface $action, $projects = array())
    {
        $this->cerbere = $cerbere;
        $this->job = $job;
        $this->action = $action;
        $this->projects = $projects;
    }

    /**
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param ActionInterface $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return Cerbere
     */
    public function getCerbere()
    {
        return $this->cerbere;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param Job $job
     */
    public function setJob($job)
    {
        $this->job = $job;
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param Project[] $projects
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;
    }
}
