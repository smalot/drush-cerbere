<?php

namespace Cerbere\Event;

use Cerbere\Action\ActionInterface;
use Cerbere\Cerbere;
use Cerbere\Model\Job;
use Cerbere\Model\Project;

/**
 * Class CerberePostActionEvent
 *
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
     * @return \Cerbere\Model\Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param \Cerbere\Model\Project[] $projects
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;
    }
}
