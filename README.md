# Cerbere

Cerbere is a Drush commands set performing action on project' modules stored in GIT, SVN or just locally.

[![Build Status](https://travis-ci.org/smalot/drush-cerbere.svg)](https://travis-ci.org/smalot/drush-cerbere)
[![Latest Stable Version](https://poser.pugx.org/smalot/cerbere/v/stable)](https://packagist.org/packages/smalot/cerbere) [![Total Downloads](https://poser.pugx.org/smalot/cerbere/downloads)](https://packagist.org/packages/smalot/cerbere) [![Latest Unstable Version](https://poser.pugx.org/smalot/cerbere/v/unstable)](https://packagist.org/packages/smalot/cerbere) [![License](https://poser.pugx.org/smalot/cerbere/license)](https://packagist.org/packages/smalot/cerbere)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smalot/drush-cerbere/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smalot/drush-cerbere/?branch=master)

# Requirements

* Composer _([install composer](https://getcomposer.org/download/))_
* Drush _([install drush](http://docs.drush.org/en/master/install/))_
* PHP 5.3+

[Compatible](https://travis-ci.org/smalot/drush-cerbere) with both Drush 7.x and 8.x.

# Installation

## Composer

You need first to download this library using `composer`.

````sh
composer global require smalot/cerbere:dev-master
````

Go to [GetComposer.org](https://getcomposer.org/download/) to install Composer on your environment.

## Bootstrap

You need to create, or alter, a file named `drushrc.php` stored in the following folder: `~/.drush`.

See [Drush documentation](https://github.com/drush-ops/drush/blob/master/docs/configure.md#drushrcphp]) for more detail on this file.

````php
<?php

// Detected composer dir according to OS platform.
if (($home_dir = getenv('HOME')) && (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
    $composer_dir = $home_dir . '/AppData/Roaming/Composer';
} else {
    $composer_dir = $home_dir . '/.composer';
}

// Include composer autoload file and declare Cerbere commands.
if (is_file($composer_dir . '/vendor/autoload.php') && 
    is_dir($composer_dir . '/vendor/smalot/cerbere/commands')) {
    include_once $composer_dir . '/vendor/autoload.php';

    $options['include'][] = $composer_dir . '/vendor/smalot/cerbere/commands';
}
````

Doing this step, Drush will be aware of commands provided by Cerbere, otherwise you'll need to use the `--include` command line option to declare the command folder each time.

````sh
drush --include=$HOME/.composer/vendor/smalot/cerbere/commands
````

# Use

## Command : cerbere-update

This report is oriented on the update of the modules. It can be enriched with the `hacked` flag which will append 2 columns indicating that a module has been locally altered or not.

* `--no-cache` : Disable cache mecanism. Otherwise, remote informations are cached for 1800 seconds.
* `--no-progress` : Disable progress bar.
* `--level` : Specify analyze verbosity (`all`, `security`, `unsupported`, `update`) - default : `all`.
* `--hacked` : Append Hacked reporting.
* `--format` : Output format (`table`, `csv`, `json`) - default : `table`.

**Example**

List all outdated module, and check if there has been hacked.
Usefull to know if module update can be safely realized.

````sh
drush cerbere-update sites/all/modules/*.info --hacked --level=update
````

## Command : cerbere-hacked

This report is dedicated to the `hacked` check. 

* `--no-progress` : Disable progress bar.
* `--format` : Output format (`table`, `csv`, `json`) - default : `table`.


# Roadmap

* Fix notices (mkdir / unlink)
* Add error handler on hacked plugin
* Add logging on hacked plugin
* Add unit tests on hacked plugin


# Credits

Parts of code has been reused from [Drupal 7.x core](https://www.drupal.org/project/drupal), [Hacked module](https://www.drupal.org/project/hacked).
