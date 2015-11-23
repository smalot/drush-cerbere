<?php

namespace Cerbere\Event;

use Cerbere\Action\ActionInterface;
use Cerbere\Model\Project;

/**
 * Class CerbereDoActionEvent
 * @package Cerbere\Event
 */
class CerbereDoActionEvent extends CerbereEvent
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
     * @param ActionInterface $action
     * @param Project $project
     */
    public function __construct(ActionInterface $action, Project $project)
    {
        $this->action = $action;
        $this->project = $project;
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
     * @return \Cerbere\Model\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Cerbere\Model\Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }
}
