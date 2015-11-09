<?php

namespace Cerbere\Action;

use Cerbere\Model\Config;
use Cerbere\Model\Part;
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
     * @param array $config
     *
     * @return mixed
     */
    public function prepare($config);

    /**
     * @param Part $part
     *
     * @return mixed
     */
    public function process(Part $part);
}
