<?php

namespace Cerbere\Versioning;

class Local implements VersioningInterface
{
    /**
     * @return string
     */
    public function getCode()
    {
        return 'local';
    }
}
