<?php

namespace Cerbere\Notification;

/**
 * Interface NotificationInterface
 *
 * @package Cerbere\Notification
 */
interface NotificationInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return mixed
     */
    public function prepare();

    /**
     * @param $report
     *
     * @return mixed
     */
    public function notify($report);
}
