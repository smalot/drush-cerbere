<?php

namespace Cerbere\Action;

use Cerbere\Model\Config;
use Cerbere\Model\Project;

/**
 * Interface ActionInterface
 *
 * @package Cerbere\Action
 */
interface ActionInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @param Config $config
     *
     * @return mixed
     */
    public function prepare(Config $config);

    /**
     * @param Project $project
     *
     * @return mixed
     */
    public function process(Project $project);
}
