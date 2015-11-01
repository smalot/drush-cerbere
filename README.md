# Cerbere

Cerbere is a Drush commands set performing action on project' modules stored in GIT, SVN or just locally.


# Installation

## Composer

You need first to download this library using `composer`.

````sh
composer global require smalot/cerbere:dev-master
````

## Bootstrap

You need to create, or alter, a file named `drushrc.php` stored in the following folder: `~/.drush`.

cf [Drush documentation](https://github.com/drush-ops/drush/blob/master/docs/configure.md#drushrcphp])

`````php
<?php

$script_name = $_SERVER['SCRIPT_NAME'];

if ($pos = strrpos($script_name, 'vendor')) {
  $dir_name = substr($script_name, 0, $pos + 6);

  $options['include'][] = $dir_name . '/smalot/cerbere/commands';
}
````

Thanks to this step, Drush will be aware of commands provided by Cerbere, otherwise you'll need to use the `--include` command line option to declare the command folder each time.

````sh
drush --include=$HOME/.composer/vendor/smalot/cerbere/commands
````
