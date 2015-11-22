<?php

namespace Cerbere\Action;

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
     * @return mixed
     */
    public function prepare();

    /**
     * @param Project[] $projects
     * @param array $options
     * @return array
     */
    public function process(array $projects, $options = array());
}
