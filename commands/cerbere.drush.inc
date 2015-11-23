<?php

/**
 * @file
 * Cerbere drush file.
 */

// Todo: remove for PROD.
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use Cerbere\Action\Checkout;
use Cerbere\Action\Update;
use Cerbere\Cerbere;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Model\Job;
use Cerbere\Parser\Info;
use Cerbere\Parser\Make;
use Cerbere\Versioning\Git;
use Cerbere\Versioning\Local;
use Cerbere\Versioning\Svn;
use Doctrine\Common\Cache\FilesystemCache;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Implements hook_drush_command().
 */
function cerbere_drush_command()
{
    $items['cerbere-update'] = array(
      'description' => 'Check updates using make file, info file or scanning folder to discover info files.',
      'arguments'   => array(
        'source' => 'The source file or folder.',
      ),
      'options'      => array(
        'config'        => 'The config file to use for default setting values.',
        'no-cache'      => 'Disable cache.',
        'level'         => 'Reporting level (all, security, unsupported, update).',
      ),
      'outputformat' => array(
        'default' => 'table',
      ),
      'examples'     => array(
        'drush cerbere-check-update sites/all/modules/ctools/ctools.info --format=json',
        'drush cerbere-check-update profiles/commerce_kickstart/drupal-core.make',
      ),
        // No bootstrap at all.
      'bootstrap'    => DRUSH_BOOTSTRAP_NONE,
    );

    return $items;
}

/**
 * Callback for the drush-demo-command command
 */
function drush_cerbere_update()
{
    $patterns = func_get_args();

/*    $config = new Config();
    $config->loadFromFile(drush_get_option('config', '.cerbere.yml'));
    $config->override('cache', !drush_get_option('no-cache', false), true);
    $config->override('level', drush_get_option('level'), 'all');
*/

    $format = drush_get_option('format', 'table');
    $flat = in_array($format, array('table', 'csv'));

    $cerbere = new Cerbere();

    // Logger.
    $log = new Logger('cerbere');
    $log->pushHandler(new StreamHandler(__DIR__ . '/logs/cerbere.log', Logger::DEBUG));
    $cerbere->addLoggerListener(new CerbereLoggerListener($log));

    // Parsers.
    $cerbere->addParser(new Make());
    $cerbere->addParser(new Info());

    // Action.
    $cache = new FilesystemCache(sys_get_temp_dir() . '/cerbere');

    $action = new Update();
    $action->setCache($cache);

    // Job.
//    $versioning = new Git();
//    $versioning->setWrapper(new \GitWrapper\GitWrapper());

//    $job = new Job();
//    $job->setVersioning($versioning);
//    $job->setSource('git://github.com/blindsidenetworks/drupal-module_bigbluebuttonbn', array());
//    $job->setPatterns(array('*.info'));

    // Run it !
    //$report = $cerbere->run($job, $action, array('flat' => $flat));

    $job = new Job();
    $job->setVersioning(new Local());
    $job->setSource('./modules/', array());
    $job->setPatterns($patterns, true);

    // Run it !
    $report = $cerbere->run($job, $action, array('flat' => $flat));

    return $report;
}
