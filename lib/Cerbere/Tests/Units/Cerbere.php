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

namespace Cerbere\Tests\Units;

use Cerbere\Action\Update;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Model\Job;
use Cerbere\Model\Project;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;
use Cerbere\Tests\AbstractTest;
use Cerbere\Versioning\Git;
use Doctrine\Common\Cache\FilesystemCache;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Cerbere
 * @package Cerbere\Tests\Units
 */
class Cerbere extends AbstractTest
{
    public function testEventDispatcher()
    {
        $cerbere = new \Cerbere\Cerbere();
        $logger = new Logger('test');
        $listener = new CerbereLoggerListener($logger);
        $cerbere->addLoggerListener($listener);
        $this->object($cerbere->getDispatcher())->isInstanceOf('\Symfony\Component\EventDispatcher\EventDispatcher');

        $dispatcher = new EventDispatcher();
        $cerbere->setDispatcher($dispatcher);
        $this->object($cerbere->getDispatcher())->isIdenticalTo($dispatcher);
    }

    public function testParser()
    {
        $cerbere = new \Cerbere\Cerbere();
        $parser = new Info();
        $this->array($cerbere->getParsers())->isEmpty();
        $cerbere->addParser($parser);
        $this->array($cerbere->getParsers())->hasSize(1);
        $this->variable($cerbere->getParser('foo'))->isNull();
        $this->object($cerbere->getParser('info'))->isIdenticalTo($parser);
    }

    public function testPatterns()
    {
        $dir = getcwd();

        $cerbere = new \Cerbere\Cerbere();
        $cerbere->addParser(new Make());
        $cerbere->addParser(new Info());

        $git = new \Cerbere\Versioning\Git();
        $git->prepare('');
        $directory = $git->getWorkingDirectory();
        $options = array(
          'arguments' => array(
            'q',
            'branch' => 'master',
            'depth'  => 1,
          ),
        );
        $git->process('https://github.com/smalot/drush-cerbere.git', $directory, $options);
        chdir($directory);

        $projects = $cerbere->getProjectsFromPatterns(array('*.info'));
        // Todo: review this point.
        $this->array($projects)->hasSize(0);
        //$this->object(reset($projects))->isInstanceOf('\Cerbere\Model\Project');

        // Restore old dir.
        chdir($dir);
    }

    public function testRun()
    {
        $cerbere = new \Cerbere\Cerbere();
        $cerbere->addParser(new Make());
        $cerbere->addParser(new Info());

        $cache = new FilesystemCache(sys_get_temp_dir() . '/cerbere');

        $action = new Update();
        $action->setCache($cache);

        $versioning = new Git();
        $versioning->setWrapper(new \GitWrapper\GitWrapper());

        $options = array(
          'arguments' => array(
            'q',
            'branch' => 'master',
            'depth'  => 1,
          ),
        );

        $job = new Job();
        $job->setVersioning($versioning);
        $job->setAction($action);
        $job->setSource('https://github.com/smalot/drush-cerbere.git', $options);
        $job->setPatterns(array('*.info'));

        $report = $cerbere->run($job);

        $expected = array(
          'cerbere' => array(
            'project'        => 'cerbere',
            'type'           => Project::TYPE_UNKNOWN,
            'version'        => '',
            'version_date'   => null,
            'recommended'    => null,
            'dev'            => null,
            'also_available' => array(),
            'status'         => -2,
            'status_label'   => 'Unknown',
            'reason'         => 'No available releases found',
          ),
        );

        // Todo: review this point.
        $this->array($report)->hasSize(0);//->isEqualTo($expected);

        $options = array(
          'arguments' => array(
            'q',
            'branch' => 'master',
            'depth'  => 1,
          ),
        );

        $job = new Job();
        $job->setVersioning($versioning);
        $job->setAction($action);
        $job->setSource('https://github.com/smalot/drush-cerbere-XXXXXX.git', $options);
        $job->setPatterns(array('*.info'));

        $this->exception(
          function () use ($cerbere, $job) {
              $cerbere->run($job);
          }
        )->message->contains('XXXXXX');
    }
}
