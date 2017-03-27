<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @package   CodeIgniter RabbitMQ Helper
 * @category  Helpers
 * @author    Romain GALLIEN
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/romainrg
 * @link      http://www.r-gallien.eu/
 *
 * CodeIgniter Helper for RabbitMQ library
 */
if (!function_exists('output_message'))
{

    /**
     * [output_message : Output defined message in Browser or Console]
     * @param  [string] $message [Output message]
     * @param  [string] $type    [Output message]
     * @return [type]            [description]
     */
    function output_message($message, $type = NULL, $symbol = '>')
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
