<?php

namespace Cerbere\Action;

use Cerbere\Model\Config;
use Cerbere\Model\Part;
use Cerbere\Model\Project;
use Cerbere\Model\ReleaseHistory;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * Class Checkout
 *
 * @package Cerbere\Action
 */
class Checkout implements ActionInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Update constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'checkout';
    }

    /**
     * @param array $config
     *
     * @return void
     */
    public function prepare($config)
    {
        $this->config = $config;
    }

    /**
     * @param Part $part
     * @param boolean $flat
     *
     * @return array|false
     */
    public function process(Part $part, $flat = false)
    {
        $part->checkoutRepository();

        if ($workingDirectory = $part->getWorkingDirectory()) {
            chdir($workingDirectory);
        }

        return array();
    }
}
