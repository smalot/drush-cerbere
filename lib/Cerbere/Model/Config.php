<?php

namespace Cerbere\Model;

use Cerbere\Service\Versioning;
use Cerbere\Versioning\AbstractVersioning;
use Symfony\Component\Yaml\Parser;

/**
 * Class Config
 * @package Cerbere\Model
 */
class Config implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var AbstractVersioning
     */
    protected $versioning;

    /**
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->data = $data;
        $this->versioning = null;
    }

    /**
     * @param $filename
     * @return \Cerbere\Model\Config
     * @throws \Exception
     */
    public function loadFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception('Missing config file.');
        }

        if (($content = file_get_contents($filename)) === false) {
            throw new \Exception('Unable to read config file.');
        }

        $parser = new Parser();
        $this->data = $parser->parse($content);
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return isset($this->data['project']) ? $this->data['project'] : 'Not specified';
    }

    /**
     * @return AbstractVersioning
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
