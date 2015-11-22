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
     * @param boolean $flat
     * @return array
     */
    public function process(Project $project, $flat = false);
}
