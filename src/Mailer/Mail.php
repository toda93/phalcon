<?php
namespace Toda\Mailer;
/**
 *
 * Sends e-mails based on pre-defined templates
 */
class Mail
{
    protected $_transport;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Sends e-mails via gmail based on predefined templates
     *
     * @param array $to
     * @param string $subject
     * @param string $name
     * @param array $params
     */
    public function send($to, $name, $subject, $body)
    {
        
        // Create the message
        $message = \Swift_Message::newInstance();

        if (is_array($name)) {
            $message->setFrom($name);
        } else {
            $message->setFrom(array(
                $this->config->from => $name
            ));
        }
        $message->setTo($to)
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        if (!$this->_transport) {
            $this->_transport = \Swift_SmtpTransport::newInstance(
                $this->config->server,
                $this->config->port,
                $this->config->security
            )
                ->setUsername($this->config->username)
                ->setPassword($this->config->password);
        }
        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($this->_transport);

        return $mailer->send($message);
    }
}