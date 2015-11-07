<?php

namespace Cerbere\Versioning;

class Svn implements VersioningInterface
{
    /**
     * @return string
     */
    public function getCode()
    {
        return 'svn';
    }
}
