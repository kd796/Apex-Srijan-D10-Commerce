<?php

namespace Drupal\step\Traits;

use Drupal\step\Traits\LoggerTrait;

trait RecordTrait
{
    use LoggerTrait;

    /*
    Details for Slack debugging
    */
    private $slack_webhook_url  = 'https://hooks.slack.com/services/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';
    private $slack_channel_name = 'wm-cleco-notices';
    private $slack_bot_name     = 'cleco-drupal'; // Name it appears in the app
    private $slack_emoji        = ':computer-rage:';

    private function record(string $message, string $type = 'error', $send_to_slack = true) {
        $this->log($message, $type);

        if ($type == 'error') {
            \Drupal::logger('step')->error($message);
        } else {
            \Drupal::logger('step')->notice($message);
        }

        if ($send_to_slack !== FALSE) {
            $slack_emoji = strtoupper(getenv('ENV')) != 'PROD' ? $this->slack_emoji : ':cleco:';
            $slack_channel_name = '#' . $this->slack_channel_name;
            $slack_bot_name = $this->slack_bot_name . ' (' . getenv('ENV') . ')';

            if (\Drupal::hasService('slack.slack_service')) {
                \Drupal::service('slack.slack_service')->sendMessage($message, $slack_channel_name, $slack_bot_name);
            } else {
                \Drupal::logger('step')->notice('Drupal Service slack.slack_service not present');
                if (function_exists('curl_init')) {
                    // build the payload
                    $data = 'payload=' . json_encode([
                        'channel'    => $slack_channel_name,
                        'text'       => $message,
                        'icon_emoji' => $slack_emoji,
                        'username'   => $slack_bot_name,
                    ]);

                    // initiate curl instance
                    $ch = curl_init($this->slack_webhook_url);

                    // setup curl options
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // send the message to Slack
                    curl_exec($ch);

                    // close the instance
                    curl_close($ch);
                }
            }
        }

        return true;
    }
}
