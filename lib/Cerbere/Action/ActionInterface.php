<?php

namespace Cerbere\Action;

use Cerbere\Model\Part;

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
     * @param boolean $flat
     * @return array
     */
    public function process(Part $part, $flat = false);
}
