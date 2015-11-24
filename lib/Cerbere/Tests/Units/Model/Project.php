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

namespace Cerbere\Tests\Units\Model;

use Cerbere\Model\Release;
use Cerbere\Tests\AbstractTest;

/**
 * Class Project
 * @package Cerbere\Tests\Units\Model
 */
class Project extends AbstractTest
{
    public function testCodeCoverage()
    {
        $project = $this->createProjectDevFromFile();

        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-3.11+29-dev');

        $this->string($project->getInstallType())->isEqualTo(\Cerbere\Model\Project::INSTALL_TYPE_DEV);
        $this->string($project->getExistingMajor())->isEqualTo('3');

        $project = new \Cerbere\Model\Project('views', '7.x', null);

        $this->string($project->getInstallType())->isEqualTo(\Cerbere\Model\Project::INSTALL_TYPE_UNKNOWN);
        $this->integer($project->getExistingMajor())->isEqualTo(-1);

        $project = new \Cerbere\Model\Project('views', '7.x', 'a.a-dev');

        $this->string($project->getInstallType())->isEqualTo(\Cerbere\Model\Project::INSTALL_TYPE_DEV);
        $this->integer($project->getExistingMajor())->isEqualTo(-1);
    }

    protected function createProjectDevFromFile()
    {
        $data = 'name = Views
description = Create customized lists and queries from your database.
package = Views
core = 7.x
php = 5.2

; Information added by Drupal.org packaging script on 2015-10-23
version = "7.x-3.11+29-dev"
core = "7.x"
project = "views"
datestamp = "1445641168"
';

        $filename = $this->createFile($data);

        $info = new \Cerbere\Parser\Info();
        $info->processFile($filename);
        $project = $info->getProject();

        return $project;
    }

    public function testGetter()
    {
        $project = $this->createProjectFromFile();

        $this->string(get_class($project))->isEqualTo('Cerbere\Model\Project');

        $details = array(
          'name'        => 'Views',
          'description' => 'Create customized lists and queries from your database.',
          'package'     => 'Views',
          'core'        => '7.x',
          'php'         => '5.2',
          'version'     => '7.x-3.11',
          'project'     => 'views',
          'datestamp'   => '1430321048',
        );
        $project->setDetails($details);

        $this->array($project->getDetails())->isEqualTo($details);

        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-3.11');
        $this->string($project->getProject())->isEqualTo('views');
        $this->string($project->getName())->isEqualTo('Views');
        $this->string($project->getDatestamp())->isEqualTo('1430321048');

        $project->setStatusUrl('http://foo');
        $this->string($project->getStatusUrl())->isEqualTo('http://foo');

        $this->string($project->getInstallType())->isEqualTo(\Cerbere\Model\Project::INSTALL_TYPE_OFFICIAL);

        $this->string($project->getExistingVersion())->isEqualTo('7.x-3.11');
        $this->string($project->getExistingMajor())->isEqualTo('3');

        $project->setStatus(\Cerbere\Model\ReleaseHistory::UPDATE_CURRENT);
        $this->integer($project->getStatus())->isEqualTo(\Cerbere\Model\ReleaseHistory::UPDATE_CURRENT);

        $project->setProjectStatus('status');
        $this->string($project->getProjectStatus())->isEqualTo('status');

        $project->setReason('my reason');
        $this->string($project->getReason())->isEqualTo('my reason');

        $project->setFetchStatus(4);
        $this->integer($project->getFetchStatus())->isEqualTo(4);

        $project->setLatestVersion('7.x-3.11');
        $project->setLatestDev('7.x-3.x-dev');
        $project->setDevVersion('7.x-2.x-dev');
        $project->setRecommended('7.x-4.0');

        $this->string($project->getLatestVersion())->isEqualTo('7.x-3.11');
        $this->string($project->getLatestDev())->isEqualTo('7.x-3.x-dev');
        $this->string($project->getDevVersion())->isEqualTo('7.x-2.x-dev');
        $this->string($project->getRecommended())->isEqualTo('7.x-4.0');

        $release = new Release(array('name' => 'views'));
        $project->setRelease('7.x-1.1', $release);
        $project->setRelease('7.x-1.2', $release);

        $this->array($project->getReleases())->size->isEqualTo(2);

        $project->setReleases(array('7.x-1.1' => $release));

        $this->array($project->getReleases())->size->isEqualTo(1);

        $this->array($project->getSecurityUpdates())->size->isEqualTo(0);
        $this->boolean($project->hasSecurityUpdates())->isFalse();

        $project->addSecurityUpdate('7.x-1.1', $release);

        $this->array($project->getSecurityUpdates())->size->isEqualTo(1);
        $this->boolean($project->hasSecurityUpdates())->isTrue();

        $project->setSecurityUpdates(array('7.x-1.1' => $release));
        $this->boolean($project->hasSecurityUpdates())->isTrue();
    }

    protected function createProjectFromFile()
    {
        $data = 'name = Views
description = Create customized lists and queries from your database.
package = Views
core = 9.x
php = 5.2

; Information added by Drupal.org packaging script on 2015-04-29
version = "7.x-8.11"
core = "8.x"
project = "views"
datestamp = "1430321048"
';

        $filename = $this->createFile($data);

        $info = new \Cerbere\Parser\Info();
        $info->processFile($filename);
        $project = $info->getProject();

        return $project;
    }
}
