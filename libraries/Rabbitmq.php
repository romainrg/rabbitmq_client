<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @package   CodeIgniter RabbitMQ Library
 * @category  Libraries
 * @author    Romain GALLIEN (romaingallien.rg@gmail.com)
 * @license   http://opensource.org/licenses/MIT > MIT License
 * @link      https://github.com/romainrg
 *
 * CodeIgniter Library for RabbitMQ interactions with CodeIgniter using PHP-AMQPLib
 */
class Rabbitmq {

    // Default private vars
    private $CI;

    // Default protected vars
    protected $config;

    // Default public vars
    public $connexion;
    public $channel;
    public $show_output;

    /**
     * __construct : Constructor
     * @method __construct
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @param  array       $config Configuration
     */
    public function __construct(array $config = array())
    {
        // Load the CI instance
        $this->CI = & get_instance();

        // Load the RabbitMQ helper
        $this->CI->load->helper('rabbitmq');

        // Define if we have to show outputs or not
        $this->show_output = (!empty($config['show_output']));

        // Define the config global
        $this->config = (!empty($config)) ? $config : array();

        // Initialize the connection
        $this->initialize($this->config);
    }

    /**
     * initialize : Initialize the configuration of the Library
     * @method initialize
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @param  array      $config Library configuration
     */
    public function initialize(array $config = array())
    {
        // We check if we have a config given then we initialize the connection
        if(!empty($config)) {
            $this->config = $config['rabbitmq'];
            $this->connexion = new PhpAmqpLib\Connection\AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass'], $this->config['vhost']);
            $this->channel = $this->connexion->channel();
        } else {
            output_message('Invalid configuration file', 'error', 'x');
        }
    }

    /**
     * push : Push an element in the specified queue
     * @method push
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @param  string  $queue                   Specified queue
     * @param  mixed(string/array)  $data       Datas
     * @param  boolean $permanent               Permanent mode of the queue
     * @param  array   $params                  Additional parameters
     * @return bool
     */
    public function push($queue = null, $data = null, $permanent = false, $params = array())
    {
        // We check if the queue is not empty then we declare the queue
        if(!empty($queue)) {

            // We declare the queue
            $this->channel->queue_declare($queue, false, $permanent, false, false, false, null, null);

            // If the informations given are in an array, we convert it in json format
            $data = (is_array($data)) ? json_encode($data) : $data;

            // Create a new instance of message then push it into the selected queue
            $item = new PhpAmqpLib\Message\AMQPMessage($data, $params);

            // Publish to the queue
            $this->channel->basic_publish($item, '', $queue);

            // Output
            ($this->show_output) ? output_message('Pushing "'.$item->body.'" to "'.$queue.'" queue -> OK', null, '+') : true;
        } else {
            output_message('You did not specify the [queue] parameter', 'error', 'x');
        }
    }

    /**
     * pull : Get the items from the specified queue (Must be executed with CLI command at this time)
     * @method pull
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @param  string  $queue     Specified queue
     * @param  bool    $permanent Permanent mode of the queue
     * @param  array   $callback  Callback
     */
    public function pull($queue = null, $permanent = false, array $callback = array())
    {
        // We check if the queue is not empty then we declare the queue
        if(!empty($queue)) {

            // Declaring the queue again
            $this->channel->queue_declare($queue, false, $permanent, false, false, false, null, null);

            // Limit the number of unacknowledged
            $this->channel->basic_qos(null, 1, null);

            // Define consuming with 'process' callback
            $this->channel->basic_consume($queue, '', false, false, false, false, $callback);

            // Continue the process of CLI command, waiting for others instructions
            while (count($this->channel->callbacks)) {
                $this->channel->wait();
            }
        } else {
            output_message('You did not specify the [queue] parameter', 'error', 'x');
        }
    }

    /**
     * Lock a message
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     * @param AMQPMessage $message
     */
    public function lock($message)
    {
        $this->channel->basic_reject($message->delivery_info['delivery_tag'], true);
    }

    /**
     * Release a message
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     * @param AMQPMessage $msg
     */
    public function unlock($message)
    {
        $this->channel->basic_ack($message->delivery_info['delivery_tag']);
    }

    /**
     * move : Move a message from a queue to another one
     * @method move
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     */
    public function move()
    {
        show_error('This method does not exist', null, 'RabbitMQ Library Error');
    }

    /**
     * purge : Delete everything in the selected queue
     * @method purge
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     * @param  string  $queue
     */
    public function purge($queue = null)
    {
        show_error('This method does not exist', null, 'RabbitMQ Library Error');
    }

    /**
     * __destruct : Close the channel and the connection
     * @method __destruct
     * @author Romain GALLIEN <romaingallien.rg@gmail.com>
     */
    public function __destruct()
    {
        // Close the channel
        if(!empty($this->channel)) {
            $this->channel->close();
        }

        // Close the connexion
        if(!empty($this->connexion)) {
            $this->connexion->close();
        }
    }
}

/* End of file Rabbitmq.php */
/* Location: ./application/librairies/Rabbitmq.php */
