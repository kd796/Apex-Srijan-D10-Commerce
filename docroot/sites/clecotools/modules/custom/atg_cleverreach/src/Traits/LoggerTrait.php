<?php

namespace Drupal\atg_cleverreach\Traits;

use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Config;
use Drupal\Core\Site\Settings;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

trait LoggerTrait
{
    /**
     * @var mixed
     */
    protected $twig;

    /**
     * @var mixed
     */
    protected $logger;

    /**
     * @var mixed
     */
    protected $mailer;

    /**
     * @var array
     */
    protected $to = ['kdouglass@webbmason.com'];

    /**
     * @var array
     */
    protected $from = ['no-reply@clecotools.test' => 'Cleco Website'];

    /**
     * @var string
     */
    protected $subject = 'ATG Website';

    /**
     * @param string $msg
     * @param string $level warning, error, debug, info, notice, critical, alert, emergency
     */
    public function log(string $msg, string $level = 'info')
    {
        if ($this->logger == null) {
            $this->logger = new Logger('CleverReach');
            $this->logger->pushHandler(new StreamHandler('sites/clecotools/files/logs/cleverreach.log', Logger::DEBUG));
        }

        $this->logger->{$level}($msg);

        if ($level == 'error') {
            \Drupal::logger('atg_cleverreach')->error($msg);
        } else {
            \Drupal::logger('atg_cleverreach')->notice($msg);
        }
    }
}
