<?php

namespace Cerbere\Versioning;

class Git implements VersioningInterface
{
    /**
     * @return string
     */
    public function getCode()
    {
        return 'git';
    }
}
