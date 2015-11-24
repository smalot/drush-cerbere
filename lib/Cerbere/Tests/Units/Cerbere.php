<?php

namespace Cerbere\Tests\Units;

use Cerbere\Action\Update;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Model\Job;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;
use Cerbere\Tests\AbstractTest;
use Cerbere\Versioning\Git;
use Doctrine\Common\Cache\FilesystemCache;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
        $this->array($projects)->hasSize(1);
        $this->object(reset($projects))->isInstanceOf('\Cerbere\Model\Project');

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
        $job->setSource('https://github.com/smalot/drush-cerbere.git', $options);
        $job->setPatterns(array('*.info'));

        $report = $cerbere->run($job, $action);

        $expected = array(
          'cerbere' => array(
            'project'      => 'cerbere',
            'version'      => '',
            'version_date' => null,
            'recommended'  => null,
            'dev'          => null,
            'status'       => -2,
            'status_label' => 'Unknown',
            'reason'       => 'No available releases found',
          ),
        );

        $this->array($report)->hasSize(1)->isEqualTo($expected);

        $options = array(
          'arguments' => array(
            'q',
            'branch' => 'master',
            'depth'  => 1,
          ),
        );

        $job = new Job();
        $job->setVersioning($versioning);
        $job->setSource('https://github.com/smalot/drush-cerbere-XXXXXX.git', $options);
        $job->setPatterns(array('*.info'));

        $this->exception(
          function () use ($cerbere, $job, $action) {
              $cerbere->run($job, $action);
          }
        )->message->contains('XXXXXX');
    }
}
