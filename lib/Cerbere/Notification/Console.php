<?php

namespace Cerbere\Notification;

/**
 * Class Console
 *
 * @package Cerbere\Notification
 */
class Console implements NotificationInterface
{
    /**
     * @return string
     */
    public function getCode()
    {
        return 'console';
    }

    /**
     * @return void
     */
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }

    /**
     * @param $report
     *
     * @return void
     */
    public function notify($report)
    {
        // TODO: Implement notify() method.
    }
}
