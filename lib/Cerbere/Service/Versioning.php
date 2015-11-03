<?php

namespace Cerbere\Service;

use Cerbere\Versioning\AbstractVersioning;
use Cerbere\Versioning\Git;
use Cerbere\Versioning\Local;
use Cerbere\Versioning\Svn;

/**
 * Class Versioning
 * @package Cerbere
 */
class Versioning
{
    /**
     * @param string $type
     * @param array $config
     * @return AbstractVersioning
     * @throws \Exception
     */
    public static function factory($type, $config = array())
    {
        switch (strtolower($type)) {
            case 'git':
                return new Git($config);
            case 'svn':
                return new Svn($config);
            case 'local':
                return new Local($config);
            default:
                throw new \Exception('Invalid VCS type.');
        }
    }
}
