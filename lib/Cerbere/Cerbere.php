<?php

namespace Cerbere;

use Cerbere\Action\ActionInterface;
use Cerbere\Event\CerbereLoggerListener;
use Cerbere\Model\Config;
use Cerbere\Model\Part;
use Cerbere\Model\Project;
use Cerbere\Parser\ParserInterface;
use Cerbere\Versioning\VersioningInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Cerbere
 *
 * @package Cerbere
 */
class Cerbere
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var VersioningInterface[]
     */
    protected $versionings;

    /**
     * @var ParserInterface[]
     */
    protected $parsers;

    /**
     * @var ActionInterface[]
     */
    protected $actions;

    /**
     * @var Part[]
     */
    protected $parts;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->versionings = array();
        $this->parsers = array();
        $this->actions = array();
    }

    /**
     * @param ActionInterface $action
     *
     * @return $this
     */
    public function addAction(ActionInterface $action)
    {
        $this->actions[$action->getCode()] = $action;

        return $this;
    }

    /**
     * @param CerbereLoggerListener $listener
     *
     * @return $this
     */
    public function addLoggerListener(CerbereLoggerListener $listener)
    {
        $this->getDispatcher()->addSubscriber($listener);

        return $this;
    }

    /**
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The Symfony event dispatcher object.
     *
     * @return $this
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @param ParserInterface $parser
     *
     * @return $this
     */
    public function addParser(ParserInterface $parser)
    {
        $this->parsers[$parser->getCode()] = $parser;

        return $this;
    }

    /**
     * @param VersioningInterface $versioning
     *
     * @return $this
     */
    public function addVersioning(VersioningInterface $versioning)
    {
        $this->versionings[$versioning->getCode()] = $versioning;

        return $this;
    }

    /**
     * @return ParserInterface[]
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * @param string $versioning
     *
     * @return VersioningInterface
     * @throws \DomainException
     */
    public function getVersioning($versioning)
    {
        if (isset($this->versionings[$versioning])) {
            return $this->versionings[$versioning];
        }

        throw new \DomainException('Unregistered versioning');
    }

    /**
     * @param string $action
     * @param boolean $flat
     *
     * @return $this
     */
    public function run($action, $flat = false)
    {
        /** @var ActionInterface $action */
        $action = $this->getAction($action);
        $report = array();
        $currentDirectory = getcwd();

        foreach ($this->getParts() as $part_name => $part) {
            drush_print('part: ' . $part->getTitle());

            $checkout = $this->getAction('checkout');
            $checkout->process($part);

            if ($result = $action->process($part, $flat)) {
                $report = array_merge($report, $result);
            }

            // Restore initial directory.
            chdir($currentDirectory);
        }

        return $report;
    }

    /**
     * @return Part[]
     */
    public function getParts()
    {
        if (is_null($this->parts)) {
            $this->parts = array();

            foreach ($this->config['parts'] as $name => $part) {
                $this->parts[$name] = Part::generateFromConfig($this, $part);
            }
        }

        return $this->parts;
    }

    /**
     * @param Part[] $parts
     *
     * @return $this
     */
    public function setParts($parts)
    {
        $this->parts = $parts;

        return $this;
    }

    /**
     * @param string $action
     *
     * @return ActionInterface
     * @throws \DomainException
     */
    public function getAction($action)
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        }

        throw new \DomainException('Unregistered action');
    }
}
