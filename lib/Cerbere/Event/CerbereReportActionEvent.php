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
use Cerbere\Model\Project;

/**
 * Class CerbereReportActionEvent
 * @package Cerbere\Event
 */
class CerbereReportActionEvent extends CerbereEvent
{
    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var array
     */
    protected $report;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param ActionInterface $action
     * @param Project $project
     * @param array $report
     * @param array $options
     */
    public function __construct(ActionInterface $action, Project $project, $report, $options = array())
    {
        $this->action = $action;
        $this->project = $project;
        $this->report = $report;
        $this->options = $options;
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
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return array
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param array $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
