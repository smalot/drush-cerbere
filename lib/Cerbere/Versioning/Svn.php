<?php

namespace Cerbere\Versioning;

/**
 * Class Svn
 *
 * @package Cerbere\Versioning
 */
class Svn implements VersioningInterface
{
    const SVN_BINARY_PATH = '/usr/bin/svn';

    /**
     * @var string
     */
    protected $workDirectory;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'svn';
    }

    /**
     * @return string
     */
    public function getWorkingDirectory()
    {
        return $this->workDirectory;
    }

    /**
     * @param string $source
     *
     * @return mixed
     */
    public function prepare($source)
    {
        $this->workDirectory = drush_tempdir();
    }

    /**
     * @param string $source
     * @param string $destination
     * @param array $options
     *
     * @return string
     */
    public function process($source, $destination, $options = array())
    {
        $command = $this->buildCommandLine($source, $destination, $options);

        if (!empty($options['debug'])) {
            drush_print('$> ' . $command);
        }

        passthru($command);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param array $options
     *
     * @return string
     */
    public function buildCommandLine($source, $destination, $options = array())
    {
        $options += array('svn' => self::SVN_BINARY_PATH, 'arguments' => array());

        $command = escapeshellarg($options['svn']) . ' ' .
          'checkout ' . escapeshellarg($source) . ' ' .
          escapeshellarg($destination) . ' ';

        foreach ($options['arguments'] as $param => $value) {
            if (is_numeric($param)) {
                $command .= escapeshellarg('-' . $value) . ' ';
            } else {
                $command .= '--' . $param . '=' . escapeshellarg($value) . ' ';
            }
        }

        return $command;
    }
}
