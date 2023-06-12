<?php

namespace Drupal\step\Traits;

use Drupal\Core\Config;
use Drupal\Core\Site\Settings;
use Drupal\Core\Template\TwigEnvironment;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_Message;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

trait LoggerTrait
{
    /**
     * @var array
     */
    protected $to = ['kevin.douglass@cstraight.com'];

    /**
     * @var array
     */
    protected $from = ['no-reply@10.110.60.7' => 'Cleco Website'];

    /**
     * @var string
     */
    protected $subject = 'ATG Website';

    /**
     * Server needs ability to connect to remote or local SMPT server (Mandrill, SendGrid, Amazon SES)
     * Needs sendmail installed
     *
     * @param string $body
     * @param string $intensity
     */
    public function email(string $body, string $intensity = 'Default')
    {
        // Load config to get mailer settings
        $config = Yaml::parse(file_get_contents(DRUPAL_ROOT . '/modules/custom/step/step.config.yml'));

        // Set up mailer
        $transport = (new Swift_SmtpTransport($config['mailer_host'], $config['mailer_port'], $config['mailer_encryption']))
            ->setUsername($config['mailer_username'])
            ->setPassword($config['mailer_password']);

        $mailer = new Swift_Mailer($transport);

        $twig     = \Drupal::service('twig');
        $template = $twig->loadTemplate(
            drupal_get_path('module', 'step') . '/templates/emails/logger.html.twig'
        );

        $message = (new Swift_Message($this->subject . ' ' . $intensity . ' Log'))
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setBody(
                $template->render([
                    'name' => $this->subject,
                    'body' => $body
                ]),
                'text/html'
            );

        $mailer->send($message);
    }

    /**
     * @param string $msg
     * @param string $level warning, error, debug, info, notice, critical, alert, emergency
     */
    public function log(string $msg, string $level = 'info')
    {
        $logger = new Logger('STEP');
        $logger->pushHandler(new StreamHandler('logs/step.log', Logger::DEBUG));

        $logger->{$level}($msg);
    }
}
