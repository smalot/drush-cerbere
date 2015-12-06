<?php

namespace Cerbere\Model\Hacked;

use Cerbere\Model\Project;

/**
 * Base class for downloading remote versions of projects.
 */
class HackedProjectWebDownloader
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * Constructor, pass in the project this downloaded is expected to download.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Returns a temp directory to work in.
     *
     * @param string $namespace
     *   The optional namespace of the temp directory, defaults to the classname.
     * @return string
     */
    protected function getTempDirectory($namespace = null)
    {
        if (null === $namespace) {
            $namespace = get_class($this);
        }

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hacked-cache';

        if (!empty($namespace)) {
            $dir .= DIRECTORY_SEPARATOR . preg_replace('/[^0-9A-Z\-_]/i', '', $namespace);
        }

        @mkdir($dir, 0775, true);

        return $dir;
    }

    /**
     * Returns a directory to save the downloaded project into.
     * @return string
     */
    protected function getDestination()
    {
        $type = $this->project->getProjectType();
        $name = $this->project->getProject();
        $version = $this->project->getVersion();

        $dir = $this->getTempDirectory() . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $name;

        // Build the destination folder tree if it doesn't already exists.
        @mkdir($dir, 0775, true);

        return $dir . DIRECTORY_SEPARATOR . $version;
    }

    /**
     * Returns the final destination of the unpacked project.
     * @return string
     */
    public function getFinalDestination()
    {
        $dir = $this->getDestination();

        return $dir;
    }

    /**
     * Download the remote files to the local filesystem.
     * @return bool
     */
    public function downloadFile()
    {
        return true;
    }

    /**
     * Recursively delete all files and folders in the specified filepath, then
     * delete the containing folder.
     *
     * Note that this only deletes visible files with write permission.
     *
     * @param string $path
     *   A filepath relative to file_directory_path.
     */
    protected function removeDir($path)
    {
        if (is_file($path) || is_link($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            $d = dir($path);

            while (($entry = $d->read()) !== false) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $entry_path = $path . DIRECTORY_SEPARATOR . $entry;
                $this->removeDir($entry_path);
            }

            $d->close();
            rmdir($path);
        }
    }
}
