<?php

namespace Cerbere\Notification;

/**
 * Class Console
 * @package Cerbere\Notification
 */
class Mail implements NotificationInterface
{
    const TRANSPORT_SMTP = 'smtp';

    const TRANSPORT_SENDMAIL = 'sendmail';

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
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function prepare()
    {
        $this->transport = self::getTransport($this->config['transport']);
    }

    /**
     * @param array $report
     * @return int
     * @throws \Exception
     */
    public function notify($report)
    {
        $this->message = \Swift_Message::newInstance()
          // Give the message a subject
          ->setSubject('Your subject')
          // Set the From address with an associative array
          ->setFrom(array('john@doe.com' => 'John Doe'))
          // Set the To addresses with an associative array
          ->setTo(array('sebastien@malot.fr', 'smalot@actualys.com' => 'Seb. Malot'))
          // Give it a body
          ->setBody('Here is the message itself');

        // Send the message
        return $this->transport->send($this->message);
    }

    /**
     * @param array $config
     * @return \Swift_Transport
     * @throws \Exception
     */
    protected static function getTransport($config)
    {
        switch (strtolower($config['type'])) {
            case Mail::TRANSPORT_SMTP:
                $host = !empty($config['host']) ? $config['host'] : 'localhost';
                $port = !empty($config['port']) ? $config['port'] : 25;
                $security = null;

                return \Swift_SmtpTransport::newInstance($host, $port, $security);

            case Mail::TRANSPORT_SENDMAIL;
                $command = !empty($config['command']) ? $config['command'] : '/usr/sbin/sendmail -bs';

                return \Swift_SendmailTransport::newInstance($command);

            default:
                throw new \Exception('Invalid or unsupported transport type.');
        }
    }
}
