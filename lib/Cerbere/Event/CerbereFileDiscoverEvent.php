<?php

/**
 * Drush Cerbere command line tools.
 * Copyright (C) 2015 - Sebastien Malot <sebastien@malot.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Cerbere\Event;

use Cerbere\Cerbere;
use Cerbere\Parser\ParserInterface;

/**
 * Class CerbereFileDiscoverEvent
 * @package Cerbere\Event
 */
class CerbereFileDiscoverEvent extends CerbereEvent
{
    /**
     * @var Cerbere
     */
    protected $cerbere;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @param Cerbere $cerbere
     * @param string $filename
     * @param ParserInterface $parser
     */
    public function __construct(Cerbere $cerbere, $filename, ParserInterface $parser)
    {
        $this->cerbere = $cerbere;
        $this->filename = $filename;
        $this->parser = $parser;
    }

    /**
     * @return Cerbere
     */
    public function getCerbere()
    {
        return $this->cerbere;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
    }
}
