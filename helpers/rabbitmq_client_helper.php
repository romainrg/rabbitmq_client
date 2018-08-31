<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter RabbitMQ Helper
 * @package   Rabbitmq_client
 * @category  Helpers
 * @author    Romain GALLIEN
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://git.santiane.io/library/rabbitmq_client
 *
 * CodeIgniter Helper for RabbitMQ library
 */
if (!function_exists('rabbitmq_client_output'))
{
    /**
     * [output_message : Output defined message in Browser or Console]
     * @param  [string] $message [Output message]
     * @param  [string] $type    [Output message]
     * @param  string $symbol
     */
    function rabbitmq_client_output($message, $type = NULL, $symbol = '>')
    {
        if(get_instance()->input->is_cli_request()) {
            switch ($type) {
                case 'error':
                echo '[x] RabbitMQ Library Error : '.$message . PHP_EOL;
                break;

                default:
                echo '['.$symbol.'] '.$message . PHP_EOL;
                break;
            }
        } else {
            switch ($type) {
                case 'error':
                    show_error($message, NULL, 'RabbitMQ Library Error');
                break;

                default:
                echo $message . '<br>';
                break;
            }
        }
    }
}
