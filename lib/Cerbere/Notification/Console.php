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
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $color;

    /**
     * @return string
     */
    public function getCode()
    {
        return 'console';
    }

    /**
     * @return boolean
     */
    public function isColor()
    {
        return $this->color;
    }

    /**
     * @param boolean $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @param Config $config
     *
     * @return mixed
     */
    public function prepare(Config $config)
    {
        $this->config = $config;
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
                    $project_name = $report_line['project_name'] . ' (' . $report_line['project'] . ')';

                    $line = str_pad($project_name, 60, ' ', STR_PAD_RIGHT);
                    $line .= str_pad($report_line['version'], 20, ' ', STR_PAD_RIGHT);
                    $line .= str_pad($report_line['recommended'], 20, ' ', STR_PAD_RIGHT);

                    if ($report_line['status'] != ReleaseHistory::UPDATE_CURRENT) {
                        // cf: https://wiki.archlinux.org/index.php/Color_Bash_Prompt
                        if ($this->isColor()) {
                            if ($report_line['status'] == ReleaseHistory::UPDATE_NOT_SECURE) {
                                $line .= "\e[0;31m";
                            } elseif ($report_line['status'] == ReleaseHistory::UPDATE_NOT_CURRENT) {
                                $line .= "\e[0;33m";
                            }
                        }

                        $line .= $report_line['status_label'];
                        if ($report_line['reason']) {
                            $line .= ' (' . $report_line['reason'] . ')';
                        }

                        if ($this->isColor()) {
                            $line .= "\e[0m";
                        }
                    }

                    drush_print($line);
                    break;
            }
        }
    }
}
