<?php

namespace Cerbere\Model\Hacked;

use Cerbere\Model\Project;

/**
 * Encapsulates a Hacked! project.
 *
 * This class should handle all the complexity for you, and so you should be able to do:
 * <code>
 * $project = hackedProject('context');
 * $project->compute_differences();
 * </code>
 *
 * Which is quite nice I think.
 */
class HackedProject
{
    const STATUS_UNCHECKED = 1;

    const STATUS_PERMISSION_DENIED = 2;

    const STATUS_HACKED = 3;

    const STATUS_DELETED = 4;

    const STATUS_UNHACKED = 5;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var array
     */
    protected $project_info = array();

    /**
     * @var HackedProjectWebDownloader
     */
    protected $remote_files_downloader;

    /**
     * @var HackedFileGroup
     */
    protected $remote_files;

    /**
     * @var HackedFileGroup
     */
    protected $local_files;

    /**
     * @var string
     */
    protected $local_folder;

    /**
     * @var string
     */
    protected $project_type = '';

    /**
     * @var string
     */
    protected $existing_version = '';

    /**
     * @var array
     */
    protected $result = array();

    /**
     * @var bool
     */
    protected $project_identified = false;

    /**
     * @var bool
     */
    protected $remote_downloaded = false;

    /**
     * @var bool
     */
    protected $remote_hashed = false;

    /**
     * @var bool
     */
    protected $local_hashed = false;

    /**
     * Constructor.
     * @param Project $project
     * @param string $local_folder
     */
    public function __construct(Project $project, $local_folder = null)
    {
        if (null === $local_folder) {
            $local_folder = getcwd();
        }
        $this->project = $project;
        $this->local_folder = $local_folder;
        $this->name = $project->getProject();
        $this->remote_files_downloader = new HackedProjectWebFilesDownloader($project);
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Get the Human readable title of this project.
     */
    public function getTitle()
    {
        $this->identifyProject();

        return isset($this->project_info['title']) ? $this->project_info['title'] : $this->name;
    }

    /**
     * Identify the project from the name we've been created with.
     *
     * We leverage the update (status) module to get the data we require about
     * projects. We just pull the information in, and make descisions about this
     * project being from CVS or not.
     */
    public function identifyProject()
    {
        // Only do this once, no matter how many times we're called.
        if (!empty($this->project_identified)) {
            return;
        }

        $data = (array) $this->project;
        $this->project_info = array();

        foreach ($data as $key => $value) {
            $key = str_replace('*', '', $key);
            $this->project_info[$key] = $value;
        }

        $this->project_info['releases'] = $this->project->getReleases();
        $this->project_identified = true;
        $this->existing_version = $this->project->getExistingVersion();
        $this->project_type = 'module';
        $this->project_info['includes'] = array($this->name . '.module');
    }

    /**
     * Downloads the remote project to be hashed later.
     */
    public function downloadRemoteProject()
    {
        // Only do this once, no matter how many times we're called.
        if (!empty($this->remote_downloaded)) {
            return;
        }

        $this->identifyProject();
        $this->remote_downloaded = (bool) $this->remote_files_downloader->downloadFile();
    }

    /**
     * Hashes the remote project downloaded earlier.
     */
    public function hashRemoteProject()
    {
        // Only do this once, no matter how many times we're called.
        if (!empty($this->remote_hashed)) {
            return;
        }

        // Ensure that the remote project has actually been downloaded.
        $this->downloadRemoteProject();

        // Set up the remote file group.
        $base_path = $this->remote_files_downloader->getFinalDestination();
        $this->remote_files = HackedFileGroup::createFromDirectory($base_path);
        $this->remote_files->computeHashes();

        $this->remote_hashed = count($this->remote_files->getFiles()) > 0;

        // Logging.
        if (!$this->remote_hashed) {
            //throw new \Exception('Could not hash remote project: ' . $this->getTitle());
            echo 'Could not hash remote project: ' . $this->getTitle() . "\n";
        }
    }

    /**
     * Locate the base directory of the local project.
     */
    public function locateLocalProject()
    {
        // we need a remote project to do this :(
        $this->hashRemoteProject();

        $project_type = $this->project->getProjectType();

        if ($project_type == Project::TYPE_PROJECT_CORE || $project_type == Project::TYPE_PROJECT_DISTRIBUTION) {
            $folder = dirname(dirname($this->local_folder));
        } else {
            $folder = $this->local_folder;
        }

        return realpath($folder);
    }

    /**
     * Hash the local version of the project.
     */
    public function hashLocalProject()
    {
        // Only do this once, no matter how many times we're called.
        if (!empty($this->local_hashed)) {
            return;
        }

        $location = $this->locateLocalProject();
        $this->local_files = HackedFileGroup::createFromList($location, $this->remote_files->getFiles());
        $this->local_files->computeHashes();
        $this->local_hashed = count($this->local_files->getFiles()) > 0;

        // Logging.
        if (!$this->local_hashed) {
            //throw new \Exception('Could not hash remote project: ' . $this->getTitle());
            echo 'Could not hash local project: ' . $this->getTitle() . "\n";
        }
    }

    /**
     * Compute the differences between our version and the canonical version of the project.
     */
    public function computeDifferences()
    {
        // Make sure we've hashed both remote and local files.
        $this->hashRemoteProject();
        $this->hashLocalProject();

        $results = array(
          'same'          => array(),
          'different'     => array(),
          'missing'       => array(),
          'access_denied' => array(),
        );

        // Now compare the two file groups.
        foreach ($this->remote_files->getFiles() as $file) {
            if ($this->remote_files->getFileHash($file) == $this->local_files->getFileHash($file)) {
                $results['same'][] = $file;
            } elseif (!$this->local_files->fileExists($file)) {
                $results['missing'][] = $file;
            } elseif (!$this->local_files->isReadable($file)) {
                $results['access_denied'][] = $file;
            } else {
                $results['different'][] = $file;
            }
        }

        $this->result = $results;
    }

    /**
     * Return a nice report, a simple overview of the status of this project.
     */
    public function computeReport()
    {
        // Ensure we know the differences.
        $this->computeDifferences();

        // Do some counting
        $report = array(
          'project_name' => $this->name,
          'status'       => self::STATUS_UNCHECKED,
          'counts'       => array(
            'same'          => count($this->result['same']),
            'different'     => count($this->result['different']),
            'missing'       => count($this->result['missing']),
            'access_denied' => count($this->result['access_denied']),
          ),
          'title'        => $this->getTitle(),
        );

        // Add more details into the report result (if we can).
        $details = array(
          'link',
          'name',
          'existing_version',
          'install_type',
          'datestamp',
          'project_type',
          'includes',
        );

        foreach ($details as $item) {
            if (isset($this->project_info[$item])) {
                $report[$item] = $this->project_info[$item];
            }
        }

        if ($report['counts']['access_denied'] > 0) {
            $report['status'] = self::STATUS_PERMISSION_DENIED;
        } elseif ($report['counts']['missing'] > 0) {
            $report['status'] = self::STATUS_HACKED;
        } elseif ($report['counts']['different'] > 0) {
            $report['status'] = self::STATUS_HACKED;
        } elseif ($report['counts']['same'] > 0) {
            $report['status'] = self::STATUS_UNHACKED;
        }

        return $report;
    }

    /**
     * Return a nice detailed report.
     * @return array
     */
    public function computeDetails()
    {
        // Ensure we know the differences.
        $report = $this->computeReport();

        $report['files'] = array();

        // Add extra details about every file.
        $states = array(
          'access_denied' => self::STATUS_PERMISSION_DENIED,
          'missing'       => self::STATUS_DELETED,
          'different'     => self::STATUS_HACKED,
          'same'          => self::STATUS_UNHACKED,
        );

        foreach ($states as $state => $status) {
            foreach ($this->result[$state] as $file) {
                $report['files'][$file] = $status;
                $report['diffable'][$file] = $this->fileIsDiffable($file);
            }
        }

        return $report;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function fileIsDiffable($file)
    {
        $this->hashRemoteProject();
        $this->hashLocalProject();

        return $this->remote_files->isNotBinary($file) && $this->local_files->isNotBinary($file);
    }

    /**
     * @param string $storage
     * @param string $file
     *
     * @return bool|string
     */
    public function getFileLocation($storage = 'local', $file)
    {
        switch ($storage) {
            case 'remote':
                $this->downloadRemoteProject();

                return $this->remote_files->getFileLocation($file);
            case 'local':
                $this->hashLocalProject();

                return $this->local_files->getFileLocation($file);
        }

        return false;
    }

    /**
     * @param string $status
     * @return string
     */
    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_PERMISSION_DENIED:
                return 'Permission denied';
            case self::STATUS_HACKED:
                return 'Hacked';
            case self::STATUS_DELETED:
                return 'Deleted';
            case self::STATUS_UNHACKED:
                return 'Unhacked';
            case self::STATUS_UNCHECKED:
            default:
                return 'Unchecked';
        }
    }
}
