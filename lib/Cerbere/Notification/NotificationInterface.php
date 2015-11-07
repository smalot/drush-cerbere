<?php

namespace Cerbere\Notification;

use Cerbere\Model\Config;

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
     * @param Config $config
     * @return mixed
     */
    public function prepare(Config $config);

    /**
     * @param string $type
     * @param array  $report
     *
     * @return mixed
     */
    public function notify($type, $report);
}
