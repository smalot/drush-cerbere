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

use Cerbere\Model\Hacked\HackedProject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CerbereHackedListener
 *
 * @package Cerbere\Event
 */
class CerbereHackedListener implements EventSubscriberInterface
{
    /**
     */
    public function __construct()
    {
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
          CerbereEvents::CERBERE_REPORT_ACTION => array('onReportAction', 0),
        );
    }

    /**
     * @param CerbereReportActionEvent $event
     */
    public function onReportAction(CerbereReportActionEvent $event)
    {
        $current_dir = getcwd();
        // Change current directory to the module directory.
        chdir($event->getProject()->getWorkingDirectory());

        $hacked = new HackedProject($event->getProject());
        $result = $hacked->computeReport();

        // Restore previous directory.
        chdir($current_dir);

        $report  = $event->getReport();
        $options = $event->getOptions();

        // Alter report.
        if (!empty($options['flat'])) {
            $report['hacked_status'] = $result['status'];
            $report['hacked_label']  = HackedProject::getStatusLabel($result['status']);
        } else {
            $report['hacked'] = array(
              'status' => $result['status'],
              'status_label' => HackedProject::getStatusLabel($result['status']),
              'counts' => $result['counts'],
            );
        }

        $event->setReport($report);
    }
}
