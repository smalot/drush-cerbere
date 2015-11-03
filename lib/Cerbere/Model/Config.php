<?php

namespace Cerbere\Model;

use Cerbere\Versioning;
use Symfony\Component\Yaml\Parser;

/**
 * Class Config
 * @package Cerbere\Model
 */
class Config
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Versioning\AbstractVersioning
     */
    protected $versioning;

    /**
     * @param $data
     */
    private function __construct($data)
    {
        $this->data = $data;
        $this->versioning = null;
    }

    /**
     * @param $filename
     * @return \Cerbere\Model\Config
     * @throws \Exception
     */
    public static function loadFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception('Missing config file.');
        }

        if (($content = file_get_contents($filename)) === false) {
            throw new \Exception('Unable to read config file.');
        }

        $parser = new Parser();
        $data = $parser->parse($content);

        return new self($data);
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return isset($this->data['project']) ? $this->data['project'] : 'Not specified';
    }

    /**
     * @return Versioning\AbstractVersioning
     * @throws \Exception
     */
    public function getVersioning()
    {
        if (is_null($this->versioning)) {
            $type = !empty($this->data['vcs']['type']) ? $this->data['vcs']['type'] : 'local';
            $config = $this->data['vcs'];
            unset($config['type']);
            $this->versioning = Versioning::factory($type, $config);
        }

        return $this->versioning;
    }
}
