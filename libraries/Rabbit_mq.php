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
            $this->connexion = new PhpAmqpLib\Connection\AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass']);
            $this->channel = $this->connexion->channel();
        } else {
            show_error('Invalid configuration file', NULL, 'RabbitMQ Library Error');
        }
    }

    /**
     * [push : Push an element in the specified queue]
     * @param  [string]          $queue [Specified queue]
     * @param  [string OR array] $data  [Datas]
     * @return [bool]
     */
    public function push($queue = NULL, $data = NULL, $params = array()) {
        // We check if the queue is not empty then we declare the queue
        if(!empty($queue)) {
            $this->channel->queue_declare($queue, false, false, false, false);

            // If the informations given are in an array, we convert it in json format
            if(is_array($data)) {
                $data = json_encode($data);
            }

            // Create a new instance of message then push it into the selected queue
            $item = new PhpAmqpLib\Message\AMQPMessage($data, $params);
            $this->channel->basic_publish($item, '', $queue);

            return $item->body;
        } else {
            show_error('You did not specify the <b>queue</b> parameter', NULL, 'RabbitMQ Library Error');
        }
    }

    /**
     * [pull : Get the items from the specified queue]
     * @param  [string]  $queue [Specified queue]
     * @param  [int]     $limit [Limit of results]
     * @return [array]          [Results]
     */
    public function pull($queue = NULL, $limit = 1000) {
        // We check if the queue is not empty then we declare the queue
        if(!empty($queue)) {
            $this->channel->queue_declare($queue, false, false, false, false);

            // define consuming
            $this->channel->basic_consume('hello', '', false, true, false, false, array($this, 'process'));

            // TEMPORAIRE
            // $callback = function($msg) {
            //     return 'Message reçu : ' . $msg->body;
            // };

            // var_dump($this->channel->basic_consume('hello', '', false, true, false, false, $callback));

        } else {
            show_error('You did not specify the <b>queue</b> parameter', NULL, 'RabbitMQ Library Error');
        }
    }

    public function process($message) {
        var_dump($message);
        echo 'lol';
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
        $this->channel->close();
        $this->connexion->close();
    }
}

/* End of file Rabbit_mq.php */
/* Location: ./application/librairies/Rabbit_mq.php */