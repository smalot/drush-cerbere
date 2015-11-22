<?php

namespace Cerbere\Versioning;

use Cerbere\Model\Config;

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
     * @param array $config
     *
     * @return mixed
     */
    public function prepare($config);

    /**
     * @param string|null $directory
     * @return mixed
     */
    public function process($directory = null);
}
