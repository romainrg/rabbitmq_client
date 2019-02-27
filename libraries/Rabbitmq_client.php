<?php

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;

require_once RABBITMQ_CLIENT_PATH.'/config/constants.php';

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter RabbitMQ Library
 * @package   Rabbitmq_client
 * @category  Libraries
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://git.santiane.io/library/rabbitmq_client
 *
 * CodeIgniter Library for RabbitMQ interactions with CodeIgniter using PHP-AMQPLib
 */
class Rabbitmq_client {
    /**
     * CI_Controller instance
     * @var CI_Controller
     */
    private $CI;

    /**
     * Configuration of the rabbitmq connexion
     * @var array
     */
    protected $config;

    /**
     * Rabbitmq connexion
     * @var PhpAmqpLib\Connection\AMQPStreamConnection
     */
    public $connexion;

    /**
     * Rabbitmq queue management
     * @var PhpAmqpLib\Channel\AMQPChannel
     */
    public $channel;

    /**
     * Write message into the output stream
     * @var bool
     */
    public $show_output;

    /**
     * Constructor
     *
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @param  array $config Overridden configuration at startup
     */
    public function __construct(array $config = array())
    {
        $this->connexion = null;

        // Load the CI instance
        $this->CI =& get_instance();

        // Load the RabbitMQ helper
        $this->CI->load->helper('rabbitmq_client');

        // Define if we have to show outputs or not
        $this->show_output = (!empty($config['show_output']));

        // Define the config global
        $this->config = (!empty($config)) ? $config : array();

        // Initialize the connection
        try {
            $this->initialize($this->config);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Initialize the configuration of the Library
     *
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @param  array $config Overridden Configuration at runtime
     *
     * @throws
     */
    public function initialize(array $config = array())
    {
        // We check if we have a config given then we initialize the connection
        if(empty($config)) {
            rabbitmq_client_output('Invalid configuration file', 'error', 'x');
            throw new Exception("Invalid configuration file");
        }
        $this->config = $config['rabbitmq_client'];
    }

    /**
     * Connect to rabboitmq
     *
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @throws
     */
    private function connect()
    {
        register_shutdown_function(array($this, 'cleanup_connection'));
        $this->connexion = new PhpAmqpLib\Connection\AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass'], $this->config['vhost']);
        $this->channel = $this->connexion->channel();
    }

    /**
     * Push an element in the specified queue
     *
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>

     * @param  string $queue Specified queue
     * @param  mixed(string/array)  $data Data to push
     * @param  boolean $permanent Permanent mode of the queue
     * @param  array $params Additional parameters
     * @throws Exception
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function push($queue = null, $data = null, $permanent = false, $params = array())
    {
        // We check if the queue is not empty then we declare the queue
        if(empty($queue)) {
            rabbitmq_client_output('You did not specify the [queue] parameter', 'error', 'x');
            throw new Exception("You did not specify the [queue] parameter");
        }

        // Connect to rabbit
        if ($this->connexion == null) {
            $this->connect();
        }

        // We declare the queue
        $this->channel->queue_declare($queue, false, $permanent, false, false, false, null, null);

        // If the information given are in an array, we convert it in json format
        $data = (is_array($data)) ? json_encode($data) : $data;

        // Create a new instance of message then push it into the selected queue
        $item = new PhpAmqpLib\Message\AMQPMessage($data, $params);

        // Publish to the queue
        $this->channel->basic_publish($item, '', $queue);

        // Output
        ($this->show_output) ? rabbitmq_client_output('Pushing "'.$item->body.'" to "'.$queue.'" queue -> OK', null, '+') : true;
    }

    /**
     * Get the items from the specified queue
     *
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @param  string $queue Specified queue
     * @param  bool $permanent Permanent mode of the queue
     * @param  array $callback Callback to treat the data
     * @throws Exception
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function pull($queue = null, $permanent = false, $callback = array())
    {
        // We check if the queue is not empty then we declare the queue
        if(empty($queue)) {
            rabbitmq_client_output('You did not specify the [queue] parameter', 'error', 'x');
            throw new Exception("You did not specify the [queue] parameter");
        }

        try {
            // Connect to rabbit
            if ($this->connexion == null) {
                $this->connect();
            }

            // Declaring the queue again
            $this->channel->queue_declare($queue, false, $permanent, false, false, false, null, null);

            // Limit the number of unacknowledged
            $this->channel->basic_qos(null, 1, null);

            // Define consuming with 'process' callback
            $this->channel->basic_consume($queue, '', false, false, false, false, function ($message) use ($callback, $queue, $permanent) {
                // Call application treatment
                $callback($message);
            });

            // Continue the process of CLI command, waiting for others instructions
            while (count($this->channel->callbacks)) {
                $this->channel->wait($this->config['allowed_methods'], $this->config['non_blocking'], $this->config['timeout']);
            }
        } catch (Exception $e) {
            $this->cleanup_connection();
            throw $e;
        }
        $this->cleanup_connection();
    }

    /**
     * Lock a message
     *
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @param PhpAmqpLib\Message\AMQPMessage $message
     */
    public function lock($message)
    {
        $this->channel->basic_nack($message->delivery_info['delivery_tag'], false, true);
    }

    /**
     * Release a message
     *
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @param PhpAmqpLib\Message\AMQPMessage $message
     */
    public function unlock($message)
    {
        $this->channel->basic_ack($message->delivery_info['delivery_tag']);
    }

    /**
     * Delete everything in the selected queue
     *
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     *
     * @param  string  $queue to purge
     * @throws
     */
    public function purge($queue = '')
    {
        // Connect to rabbit
        if ($this->connexion == null) {
            $this->connect();
        }

        // Purge queue if exist
        if (!empty($queue)) {
            $this->channel->queue_purge($queue);
        }
    }

    /**
     * Close the channel and the connection
     *
     * @author Stéphane Lucien-Vauthier <direction@aslv-consulting.fr>
     */
    public function cleanup_connection() {
        try {
            // Close the channel
            if(!empty($this->channel)) {
                $this->channel->close();
                $this->channel = null;
            }

            // Close the connexion
            if(!empty($this->connexion)) {
                $this->connexion->close();
                $this->connexion = null;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Close the channel and the connection
     *
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     */
    public function __destruct()
    {
        $this->cleanup_connection();
    }
}

/* End of file Rabbitmq_client.php */
/* Location: ./application/libraries/Rabbitmq_client.php */
