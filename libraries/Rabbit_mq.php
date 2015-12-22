<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @package   CodeIgniter RabbitMQ Library
 * @category  Libraries
 * @author    Romain GALLIEN
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/romainrg
 * @link      http://www.r-gallien.eu/
 *
 * CodeIgniter Library for RabbitMQ interactions with CodeIgniter using PHP-AMQPLib
 */
class Rabbit_mq {

    // Default private vars
    private $CI;

    // Default protected vars
    protected $config;

    // Default public vars
    public $connexion;
    public $channel;

    /**
     * [__construct : Construct]
     */
    public function __construct() {
        // Load the CI instance
        $this->CI = & get_instance();

        // Load the RabbitMQ config then load the config as item
        $this->CI->config->load('rabbitmq');
        $this->config = $this->CI->config->item('rabbitmq');

        // Initialize the connection
        self::initialize($this->config);
    }

    /**
     * [initialize : Initialize the configuration of the Library]
     * @param  [array]  $config Library configuration
     */
    public function initialize($config = array()) {
        // We check if we have a config given then we initialize the connection
        if(!empty($config)) {
            $this->connexion = new PhpAmqpLib\Connection\AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass'], $this->config['vhost']);
            $this->channel = $this->connexion->channel();
        } else {
            if($this->CI->input->is_cli_request()) {
                echo '[x] RabbitMQ Library Error : Invalid configuration file' . PHP_EOL;
            } else {
                show_error('Invalid configuration file', NULL, 'RabbitMQ Library Error');
            }
        }
    }

    /**
     * [push : Push an element in the specified queue]
     * @param  [string]          $queue     [Specified queue]
     * @param  [string OR array] $data      [Datas]
     * @param  [bool]            $permanent [Permanent mode of the queue]
     * @param  [array]           $params    [Additional parameters]
     * @return [bool]
     */
    public function push($queue = NULL, $data = NULL, $permanent = FALSE, $params = array()) {
        // We check if the queue is not empty then we declare the queue
        if(!empty($queue)) {
            $this->channel->queue_declare($queue, FALSE, $permanent, FALSE, FALSE);

            // If the informations given are in an array, we convert it in json format
            if(is_array($data)) {
                $data = json_encode($data);
            }

            // Create a new instance of message then push it into the selected queue
            $item = new PhpAmqpLib\Message\AMQPMessage($data, $params);
            $this->channel->basic_publish($item, '', $queue);

            echo '[+] Pushing "'.$item->body.'" to "'.$queue.'" queue -> OK' . PHP_EOL;
        } else {
            if($this->CI->input->is_cli_request()) {
                echo '[x] RabbitMQ Library Error : You did not specify the [queue] parameter' . PHP_EOL;
            } else {
                show_error('You did not specify the <b>queue</b> parameter', NULL, 'RabbitMQ Library Error');
            }
        }
    }

    /**
     * [pull : Get the items from the specified queue] (Must be executed with CLI command at this time)
     * @param  [string]  $queue     [Specified queue]
     * @param  [bool]    $permanent [Permanent mode of the queue]
     */
    public function pull($queue = NULL, $permanent = FALSE) {
        // We check if the queue is not empty then we declare the queue
        if(!empty($queue)) {
            // Declaring the queue again
            $this->channel->queue_declare($queue, FALSE, $permanent, FALSE, FALSE);

            // Define the start message for CLI command
            echo '[*] Waiting for instructions, press CTRL + C to abort.' . PHP_EOL;

            // Define consuming with 'process' callback
            $this->channel->basic_consume($queue, FALSE, FALSE, TRUE, FALSE, FALSE, array($this, '_process'));

            // Continue the process of CLI command, waiting for others instructions
            while (count($this->channel->callbacks)) {
                $this->channel->wait();
            }
        } else {
            if($this->CI->input->is_cli_request()) {
                echo '[x] RabbitMQ Library Error : You did not specify the [queue] parameter' . PHP_EOL;
            } else {
                show_error('You did not specify the <b>queue</b> parameter', NULL, 'RabbitMQ Library Error');
            }
        }
    }

    /**
    * [process : Process function while pull function fetch some items]
    * @param  [object] $message [Message object]
    */
    public function _process($message) {
        echo '[>] Getting instructions : ' . $message->body . PHP_EOL;
    }

    /**
     * [move : Move a message from a queue to another one]
     */
    public function move() {
        show_error('This method does not exist', NULL, 'RabbitMQ Library Error');
    }

    /**
     * [purge : Delete everything in the selected queue]
     */
    public function purge($queue = NULL) {
        show_error('This method does not exist', NULL, 'RabbitMQ Library Error');
    }

    /**
     * [__destruct : Close the channel and the connection]
     */
    public function __destruct() {
        if(!empty($this->channel)) {
            $this->channel->close();
        }
        if(!empty($this->connexion)) {
            $this->connexion->close();
        }
    }
}

/* End of file Rabbit_mq.php */
/* Location: ./application/librairies/Rabbit_mq.php */