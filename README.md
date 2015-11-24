# Cerbere

Cerbere is a Drush commands set performing action on project' modules stored in GIT, SVN or just locally.

[![Build Status](https://travis-ci.org/smalot/drush-cerbere.svg)](https://travis-ci.org/smalot/drush-cerbere)
[![Latest Stable Version](https://poser.pugx.org/smalot/cerbere/v/stable)](https://packagist.org/packages/smalot/cerbere) [![Total Downloads](https://poser.pugx.org/smalot/cerbere/downloads)](https://packagist.org/packages/smalot/cerbere) [![Latest Unstable Version](https://poser.pugx.org/smalot/cerbere/v/unstable)](https://packagist.org/packages/smalot/cerbere) [![License](https://poser.pugx.org/smalot/cerbere/license)](https://packagist.org/packages/smalot/cerbere)

# Requirements

* Composer _([install composer](https://getcomposer.org/download/))_
* Drush _([install drush](http://docs.drush.org/en/master/install/))_
* PHP 5.3+

Compatible with both Drush 7.x and 8.x.

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

## Command

* `--no-cache` : Disable cache mecanism. Otherwise, remote informations are cached for 1800 seconds.
* `--no-progress` : Disable progress bar.
* `--level` : Specify analyze verbosity (`all`, `security`, `unsupported`, `update`) - default : `all`.
* `--format` : Output format (`table`, `csv`, `json`) - default : `table`.

Example:

````sh
drush cerbere-update sites/all/modules/*.info --level=update
````
