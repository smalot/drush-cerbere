<?php

namespace Cerbere\Action;

use Cerbere\Model\Project;

/**
 * Interface ActionInterface
 * @package Cerbere\Action
 */
interface ActionInterface
{
    /**
     * @return mixed
     */
    public function prepare();

    /**
     * @param \Cerbere\Model\Project $project
     * @return mixed
     */
    public function process(Project $project);
}
