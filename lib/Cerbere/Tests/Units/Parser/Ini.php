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

namespace Cerbere\Tests\Units\Parser;

use Cerbere\Tests\AbstractTest;

/**
 * Class Ini
 * @package Cerbere\Tests\Units\Parser
 */
class Ini extends AbstractTest
{
    /**
     *
     */
    public function testParseData()
    {
        $filename = $this->createInfoFile();
        $this->string($filename)->contains('ato');

        $info = new \Cerbere\Parser\Info($filename);
        $info->processFile($filename);
        $this->class($info);

        $project = $info->getProject();
        $this->string(get_class($project))->isEqualTo('Cerbere\Model\Project');
        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-3.11');
        $this->string($project->getName())->isEqualTo('Views');
        $this->string($project->getDatestamp())->isEqualTo('1430321048');

        $details = $project->getDetails();
        $this->string($details['styles'][0][0])->isEqualTo(PHP_VERSION);
    }

    /**
     * @return string|false
     */
    protected function createInfoFile()
    {
        $data = 'name = Views
description = Create customized lists and queries from your database.
package = Views
core = 7.x
php = 5.2

; Always available CSS
stylesheets[all][] = css/views.css

dependencies[] = ctools
; Handlers
files[] = handlers/views_handler_area.inc

; Parsing
styles[][] = PHP_VERSION

; Information added by Drupal.org packaging script on 2015-04-29
version = "7.x-3.11"
core = "7.x"
project = "views"
datestamp = "1430321048"
';

        return $this->createFile($data);
    }
}
