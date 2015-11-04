<?php

namespace Cerbere\Notification;

/**
 * Interface NotificationInterface
 * @package Cerbere\Notification
 */
interface NotificationInterface {
    /**
     * @return mixed
     */
    public function prepare();

    /**
     * @param $report
     * @return mixed
     */
    public function notify($report);
}
