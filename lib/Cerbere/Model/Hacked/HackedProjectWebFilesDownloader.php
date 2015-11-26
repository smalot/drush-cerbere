<?php

namespace Cerbere\Model\Hacked;

use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\Tar;

/**
 * Downloads a project using a standard Drupal method.
 */
class HackedProjectWebFilesDownloader extends HackedProjectWebDownloader
{
    /**
     * @return string|false
     */
    public function getDownloadLink()
    {
        $version = $this->project->getVersion();

        // Remove 'dev' tailing flag from version name.
        if (preg_match('/(\+\d+\-dev)$/', $version)) {
            $version = preg_replace('/(\+\d+\-dev)$/', '', $version);
        }

        if ($release = $this->project->getRelease($version)) {
            return $release->getDownloadLink();
        }

        return false;
    }

    /**
     * @return string|boolean
     */
    public function downloadFile()
    {
        $destination = $this->getDestination();

        if (!($release_url = $this->getDownloadLink())) {
            return false;
        }

        // If our directory already exists, we can just return the path to this cached version
        $whiteList = array('.', '..', 'CVS', '.svn', '.git');
        if (file_exists($destination) && count(HackedFileGroup::scanDirectory($destination, '/.*/', $whiteList))
        ) {
            return $destination;
        }

        // Build the destination folder tree if it doesn't already exists.
        mkdir($destination, 0775, true);

        if (!($local_file = $this->getFile($release_url))) {
            return false;
        }

        try {
            $this->extractArchive($local_file, $destination);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";

            return false;
        }

        return true;
    }

    /**
     * Copies a file from $url to the temporary directory for updates.
     *
     * If the file has already been downloaded, returns the the local path.
     *
     * @param string $url
     *   The URL of the file on the server.
     *
     * @return string
     *   Path to local file.
     */
    protected function getFile($url)
    {
        $parsed_url = parse_url($url);
        $remote_schemes = array('http', 'https', 'ftp', 'ftps', 'smb', 'nfs');

        if (!in_array($parsed_url['scheme'], $remote_schemes)) {
            // This is a local file, just return the path.
            return realpath($url);
        }

        // Todo: use Symfony's cache objects
        // Check the cache and download the file if needed.
        $cache_directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hacked-cache';
        $local = $cache_directory . DIRECTORY_SEPARATOR . basename($parsed_url['path']);

        if (!file_exists($cache_directory)) {
            mkdir($cache_directory, 0775, true);
        }

        // Todo: use guzzle.
        $content = file_get_contents($url);

        if ($content !== false && file_put_contents($local, $content)) {
            return $local;
        }

        return false;
    }

    /**
     * Unpack a downloaded archive file.
     *
     * @param string $file
     *   The filename of the archive you wish to extract.
     * @param string $directory
     *   The directory you wish to extract the archive into.
     * @return Archive
     *   The Archiver object used to extract the archive.
     * @throws \Exception on failure.
     */
    protected function extractArchive($file, $directory)
    {
        $archive = new Tar();

        // Remove the directory if it exists, otherwise it might contain a mixture of
        // old files mixed with the new files (e.g. in cases where files were removed
        // from a later release).
        $archive->open($file);
        $files = $archive->contents();

        // First entry contains the root folder.
        $project_path = $files[0]->getPath();

        if (file_exists($directory)) {
            $this->removeDir($directory);
        }

        // Reopen archive to extract all files.
        $archive->open($file);
        // Strip first folder level.
        $archive->extract($directory, $project_path);

        return $archive;
    }
}
