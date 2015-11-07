<?php

namespace Cerbere\Notification;

use Cerbere\Model\Config;
use Cerbere\Model\ReleaseHistory;

/**
 * Class Console
 *
 * @package Cerbere\Notification
 */
class Mail implements NotificationInterface
{
    const TRANSPORT_SMTP = 'smtp';

    const TRANSPORT_SENDMAIL = 'sendmail';

    /**
     * @var string
     */
    protected $project_name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Swift_Transport
     */
    protected $transport;

    /**
     * @var \Swift_Message
     */
    protected $message;

    /**
     * Mail constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'mail';
    }

    /**
     * @param Config $config
     *
     * @return mixed
     */
    public function prepare(Config $config)
    {
        $this->project_name = $config['project'];
        $this->config       = $config['notification']['mail'];

        $this->transport    = self::getTransport($this->config['transport']);
    }

    /**
     * @param array $config
     *
     * @return \Swift_Transport
     * @throws \Exception
     */
    protected static function getTransport($config)
    {
        switch (strtolower($config['type'])) {
            case (Mail::TRANSPORT_SMTP):
                $host     = !empty($config['host']) ? $config['host'] : 'localhost';
                $port     = !empty($config['port']) ? $config['port'] : 25;
                $security = !empty($config['security']) ? $config['security'] : null;

                $transport = \Swift_SmtpTransport::newInstance($host, $port, $security);
                $transport->setUsername($config['username']);
                $transport->setPassword($config['password']);

                return $transport;

            case (Mail::TRANSPORT_SENDMAIL);
                $command = !empty($config['command']) ? $config['command'] : '/usr/sbin/sendmail -bs';

                return \Swift_SendmailTransport::newInstance($command);

            default:
                throw new \Exception('Invalid or unsupported transport type: ' . strtolower($config['type']) . '.');
        }
    }

    /**
     * @param string $type
     * @param array  $report
     *
     * @return int
     * @throws \Exception
     */
    public function notify($type, $report)
    {
        $body = $this->buildBody($type, $report);

        return $this->sendMessage($body);
    }

    /**
     * @param string $type
     * @param array  $report
     *
     * @return string
     */
    protected function buildBody($type, $report)
    {
        $body = '<h3>' . $this->project_name . '</h3>';

        switch ($type) {
            case 'update':
                $body .= '<table width="100%" cellpadding="0" cellspacing="0">';
                $body .= '<tr><th style="background-color: black; color: white;">Project</th>' .
                  '<th style="background-color: black; color: white; width: 15%;">Version</th>' .
                  '<th style="background-color: black; color: white; width: 15%;">Recommended</th>' .
                  '<th style="background-color: black; color: white;">Status</th>' .
                  '</tr>';

                foreach ($report as $report_line) {
                    $body .= '<tr>' .
                      '<td>' . $report_line['project'] . '</td>' .
                      '<td style="white-space: nowrap">' . $report_line['version'] . '</td>' .
                      '<td style="white-space: nowrap">' . $report_line['recommended'] . '</td>';

                    if ($report_line['status'] != ReleaseHistory::UPDATE_CURRENT) {
                        $body .= '<td>' . $report_line['status_label'];

                        if (!empty($report_line['reason'])) {
                            $body .= ' (' . $report_line['reason'] . ')';
                        }

                        $body .= '</td>';
                    } else {
                        $body .= '<td>&nbsp;</td>';
                    }
                    $body .= '</tr>';
                }

                $body .= '</table><br/>';
                $body .= '<p>Analyze done at: '.date('Y-m-d H:i:d').' using <a href="https://github.com/smalot/drush-cerbere">Cerbere</a>.</p>';
                break;
        }

        return $body;
    }

    /**
     * @param string $body
     *
     * @return int
     */
    protected function sendMessage($body)
    {
        $message_config = $this->config['message'];

        $subject = !empty($message_config['subject']) ? $message_config['subject'] : 'Cerbere Notification System';
        $to      = is_array($message_config['to']) ? $message_config['to'] : explode(',', $message_config['to']);

        $this->message = \Swift_Message::newInstance()
          // Give the message a subject
          ->setSubject($subject)
          // Set the From address with an associative array
          ->setFrom($message_config['from'])
          // Set the To addresses with an associative array
          ->setTo($to)
          // Give it a body
          ->setBody($body, 'text/html');

        $mailer = \Swift_Mailer::newInstance($this->transport);

        // Send the message
        $status = $mailer->send($this->message);

        // Todo: check status.

        return $status;
    }
}
