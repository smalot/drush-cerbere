# Cerbere

Cerbere is a Drush commands set performing action on project' modules stored in GIT, SVN or just locally.

[![Build Status](https://travis-ci.org/smalot/drush-cerbere.svg)](https://travis-ci.org/smalot/drush-cerbere)
[![Latest Stable Version](https://poser.pugx.org/smalot/cerbere/v/stable)](https://packagist.org/packages/smalot/cerbere) [![Total Downloads](https://poser.pugx.org/smalot/cerbere/downloads)](https://packagist.org/packages/smalot/cerbere) [![Latest Unstable Version](https://poser.pugx.org/smalot/cerbere/v/unstable)](https://packagist.org/packages/smalot/cerbere) [![HHVM Status](http://hhvm.h4cc.de/badge/smalot/cerbere.png)](http://hhvm.h4cc.de/package/smalot/cerbere)
[![License](https://poser.pugx.org/smalot/cerbere/license)](https://packagist.org/packages/smalot/cerbere)

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

$script_name = $_SERVER['SCRIPT_NAME'];

if ($pos = strrpos($script_name, 'vendor')) {
  $dir_name = substr($script_name, 0, $pos + 6);

  $options['include'][] = $dir_name . '/smalot/cerbere/commands';
}
````

Doing this step, Drush will be aware of commands provided by Cerbere, otherwise you'll need to use the `--include` command line option to declare the command folder each time.

````sh
drush --include=$HOME/.composer/vendor/smalot/cerbere/commands
````
