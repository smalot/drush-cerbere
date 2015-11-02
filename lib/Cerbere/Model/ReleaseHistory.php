<?php

namespace Cerbere\Model;

/**
 * Class ReleaseHistory
 * @package Cerbere\Action
 */
class ReleaseHistory
{
    /**
     * Project is missing security update(s).
     */
    const UPDATE_NOT_SECURE = 1;

    /**
     * Current release has been unpublished and is no longer available.
     */
    const UPDATE_REVOKED = 2;

    /**
     * Current release is no longer supported by the project maintainer.
     */
    const UPDATE_NOT_SUPPORTED = 3;

    /**
     * Project has a new release available, but it is not a security release.
     */
    const UPDATE_NOT_CURRENT = 4;

    /**
     * Project is up to date.
     */
    const UPDATE_CURRENT = 5;

    /**
     * Project's status cannot be checked.
     */
    const UPDATE_NOT_CHECKED = -1;

    /**
     * No available update data was found for project.
     */
    const UPDATE_UNKNOWN = -2;

    /**
     * There was a failure fetching available update data for this project.
     */
    const UPDATE_NOT_FETCHED = -3;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param Project $project
     * @param string $url
     */
    public function __construct(Project $project, $url = null)
    {
        $this->project = $project;
        $this->url = $url;

        $this->init();
    }

    /**
     *
     */
    protected function init()
    {
        $url = $this->project->getStatusUrl() . '/' .
          $this->project->getProject() . '/' .
          $this->project->getCore();

        $content = file_get_contents($url);

        if ($data = $this->parseUpdateXml($content)) {
            foreach ($data['releases'] as $key => $value) {
                $release = new Release($value);
                $data['releases'][$key] = $release;
            }
        }

        $this->data = $data;
    }

    /**
     * Parses the XML of the Drupal release history info files.
     *
     * @param string $raw_xml
     *   A raw XML string of available release data for a given project.
     *
     * @return array
     *   Array of parsed data about releases for a given project, or NULL if there
     *   was an error parsing the string.
     */
    protected function parseUpdateXml($raw_xml)
    {
        try {
            $xml = new \SimpleXMLElement($raw_xml);
        } catch (\Exception $e) {
            // SimpleXMLElement::__construct produces an E_WARNING error message for
            // each error found in the XML data and throws an exception if errors
            // were detected. Catch any exception and return failure (NULL).
            return array();
        }

        // If there is no valid project data, the XML is invalid, so return failure.
        if (!isset($xml->short_name)) {
            return array();
        }

        $data = array();
        foreach ($xml as $k => $v) {
            $data[$k] = (string) $v;
        }
        $data['releases'] = array();

        if (isset($xml->releases)) {
            foreach ($xml->releases->children() as $release) {
                $version = (string) $release->version;
                $data['releases'][$version] = array();
                foreach ($release->children() as $k => $v) {
                    $data['releases'][$version][$k] = (string) $v;
                }
                $data['releases'][$version]['terms'] = array();
                if ($release->terms) {
                    foreach ($release->terms->children() as $term) {
                        if (!isset($data['releases'][$version]['terms'][(string) $term->name])) {
                            $data['releases'][$version]['terms'][(string) $term->name] = array();
                        }
                        $data['releases'][$version]['terms'][(string) $term->name][] = (string) $term->value;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Release
     */
    public function getLastRelease()
    {
        $release = reset($this->data['releases']);

        return $release;
    }

    /**
     * @return \Cerbere\Model\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Cerbere\Model\Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param string $version
     * @return Release
     */
    public function getRelease($version)
    {
        return $this->data['releases'][$version];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->data['short_name'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->data['type'];
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->data['api_version'];
    }

    /**
     * @return int
     */
    public function getRecommendedMajor()
    {
        return $this->data['recommended_major'];
    }

    /**
     * @return string
     */
    public function getSupportedMajors()
    {
        return $this->data['supported_majors'];
    }

    /**
     * @return int
     */
    public function getDefaultMajor()
    {
        return $this->data['default_major'];
    }

    /**
     * @return string
     */
    public function getProjectStatus()
    {
        return $this->data['project_status'];
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->data['link'];
    }

    /**
     * @return string
     */
    public function getTerms()
    {
        return trim($this->data['terms']);
    }

    /**
     * @return Release[]
     */
    public function getReleases()
    {
        return $this->data['releases'];
    }

    /**
     * @return mixed
     */
    public function getFetchStatus()
    {
        return isset($this->data['fetch_status']) ? $this->data['fetch_status'] : 0;
    }
}
