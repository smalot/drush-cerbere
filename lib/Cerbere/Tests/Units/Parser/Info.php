<?php

namespace Cerbere\Tests\Units\Parser;

use Cerbere\Tests\AbstractTest;

class Info extends AbstractTest
{
    public function testGetProject()
    {
        $filename = $this->createInfoFile();
        $this->string($filename)->contains('ato');

        $info = new \Cerbere\Parser\Info($filename);
        $this->class($info);

        $project = $info->getProject();
        $this->string(get_class($project))->isEqualTo('Cerbere\Model\Project');
        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-3.11');
        $this->string($project->getName())->isEqualTo('Views');
        $this->string($project->getDatestamp())->isEqualTo('1430321048');
    }

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

; Information added by Drupal.org packaging script on 2015-04-29
version = "7.x-3.11"
core = "7.x"
project = "views"
datestamp = "1430321048"
';

        return $this->createFile($data);
    }
}
