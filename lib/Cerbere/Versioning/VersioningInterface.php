<?php

namespace Cerbere\Versioning;

/**
 * Interface VersioningInterface
 *
 * @package Cerbere\Versioning
 */
interface VersioningInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getWorkingDirectory();

    /**
     * @param string $source
     *
     * @return mixed
     */
    public function prepare($source);

    /**
     * @param string $source
     * @param string $destination
     * @param array $options
     *
     * @return string
     */
    public function process($source, $destination, $options = array());
}
