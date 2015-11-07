<?php

namespace Cerbere\Notification;

use Cerbere\Model\Config;
use Cerbere\Model\ReleaseHistory;

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
     * @param Config $config
     * @return mixed
     */
    public function prepare(Config $config)
    {
        // TODO: Implement prepare() method.
    }

    /**
     * @param string $type
     * @param array  $report
     *
     * @return void
     */
    public function notify($type, $report)
    {
        foreach ($report as $project => $report_line) {
            switch ($type) {
                case 'update':
                    $line = str_pad($report_line['project'], 45, ' ', STR_PAD_RIGHT);
                    $line .= str_pad($report_line['version'], 20, ' ', STR_PAD_RIGHT);
                    $line .= str_pad($report_line['recommended'], 20, ' ', STR_PAD_RIGHT);

                    if ($report_line['status'] != ReleaseHistory::UPDATE_CURRENT) {
                        $line .= $report_line['status_label'];
                        if ($report_line['reason']) {
                            $line .= ' (' . $report_line['reason'] . ')';
                        }
                    }

                    drush_print($line);
                    break;
            }
        }
    }
}
