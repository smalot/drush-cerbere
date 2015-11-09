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
use Cerbere\Model\Config;
use Cerbere\Notification\Console;
use Cerbere\Notification\Mail;
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
      'options'     => array(
        'config'        => 'The config file to use for default setting values.',
        'report-format' => 'Reporting format: console (default), json, html.',
        'no-cache'      => 'Disable cache.',
        'level'         => 'Reporting level (all, security, unsupported, update).',
      ),
      'examples'    => array(
        'drush cerbere-check-update sites/all/modules/ctools/ctools.info',
        'drush cerbere-check-update profiles/commerce_kickstart/drupal-core.make',
      ),
        // No bootstrap at all.
      'bootstrap'   => DRUSH_BOOTSTRAP_NONE,
    );

    return $items;
}

/**
 * Callback for the drush-demo-command command
 */
function drush_cerbere_update()
{
    $patterns = func_get_args();

    $config = new Config();
    $config->loadFromFile(drush_get_option('config', '.cerbere.yml'));
    $config->override('cache', !drush_get_option('no-cache', false), true);
    $config->override('level', drush_get_option('level'), 'all');
    $config->override('report-format', drush_get_option('report-format'), 'console');

    $cerbere = new Cerbere($config);

    // Logger.
    $log = new Logger('cerbere');
    $log->pushHandler(new StreamHandler('cerbere.log', Logger::DEBUG));
    $cerbere->addLoggerListener(new CerbereLoggerListener($log));

    // Parsers.
    $cerbere->addParser(new Make());
    $cerbere->addParser(new Info());

    // Actions.
    $cerbere->addAction(new Checkout());
    $action = new Update();
    $action->setCache(new FilesystemCache(sys_get_temp_dir() . '/cerbere'));
    $cerbere->addAction($action);

    // Versionings.
    $cerbere->addVersioning(new Local());
    $cerbere->addVersioning(new Git());
    $cerbere->addVersioning(new Svn());

    // Notifications.
    $notification = new Console();
    $notification->setColor(true);
    $cerbere->addNotification($notification);

    $notification = new Mail();
    $transport = Mail::generateTransport($config['notifications']['mail']['transport']);
    $notification->setTransport($transport);
    $cerbere->addNotification($notification);

    // Run it !
    $cerbere->run(array('checkout', 'update'));
}
