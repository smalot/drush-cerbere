<?php

namespace Cerbere\Tests\Units\Parser;

use Cerbere\Tests\AbstractTest;

/**
 * Class Ini
 *
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
