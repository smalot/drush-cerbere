<?php

/**
 * Drush Cerbere command line tools.
 * Copyright (C) 2015 - Sebastien Malot <sebastien@malot.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Cerbere\Event;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CerbereProgressBarListener
 * @package Cerbere\Event
 */
class CerbereProgressBarListener implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * CerbereConsoleListener constructor.
     * @param OutputInterface|null $output
     */
    public function __construct(OutputInterface $output = null)
    {
        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
          CerbereEvents::CERBERE_PRE_ACTION  => array('onCerberePreAction', 0),
          CerbereEvents::CERBERE_DO_ACTION   => array('onCerbereDoAction', 0),
          CerbereEvents::CERBERE_POST_ACTION => array('onCerberePostAction', 0),
        );
    }

    /**
     * @param CerbereDoActionEvent $event
     */
    public function onCerbereDoAction(CerbereDoActionEvent $event)
    {
        $this->progress->setMessage($event->getProject()->getName(), 'project');
        $this->progress->advance();
    }

    /**
     * @param CerberePostActionEvent $event
     */
    public function onCerberePostAction(CerberePostActionEvent $event)
    {
        $this->progress->setMessage('Action ended');
        $this->progress->setMessage('', 'project');
        $this->progress->finish();

        // Returns and jump new line.
        $this->output->getErrorOutput()->writeln('');
        $this->output->getErrorOutput()->writeln('');
    }

    /**
     * @param CerberePreActionEvent $event
     */
    public function onCerberePreAction(CerberePreActionEvent $event)
    {
        // Returns and jump new line.
        $this->output->getErrorOutput()->writeln('');

        $format = " Project: %project%\n";
        $format .= ProgressBar::getFormatDefinition('debug');

        $progress = new ProgressBar($this->output, count($event->getProjects()));
        $progress->setFormat($format);
        $progress->setRedrawFrequency(1);
        $progress->setMessage('Action starts');

        $this->progress = $progress;
    }
}
