<?php
defined('BASEPATH') OR exit('No direct script access allowed');

defined('RABBITMQ_CLIENT_PATH') OR define('RABBITMQ_CLIENT_PATH', FCPATH . 'vendor/romainrg/rabbitmq_client');

/**
 * Test Rabbitmq_client
 * @package  Rabbitmq_client
 * @category Tests
 * @version  20180706
 * @property CI_Controller $CI
 */
class Test_Rabbitmq_client extends TestCase
{
    /**
     * Begin test
     * @var bool
     */
    private static $begin = true;

    /**
     * Constructor
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function __construct()
    {
        $this->activateTest = true;
        $this->stressTest = false;
        parent::__construct();

        $this->CI->load->add_package_path('vendor/romainrg/rabbitmq_client')
            ->library('rabbitmq_client')
            ->remove_package_path('vendor/romainrg/rabbitmq_client');
    }

    /**
     * Reset data before each test
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @param bool $hasData if true, insert data to queue
     * @throws Exception
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function beforeTest($hasData = true)
    {
        if (!self::$begin) {
            $this->CI->rabbitmq_client->purge('test');
        }
        self::$begin = false;
        if ($hasData) {
            $this->CI->rabbitmq_client->push('test', json_encode(array('id' => 1, 'name' => "initial data 1")), false, array('delivery_mode' => 2));
            $this->CI->rabbitmq_client->push('test', json_encode(array('id' => 2, 'name' => "initial data 2")), false, array('delivery_mode' => 2));
        }
    }

    /**
     * Test push to rabbitmq
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @throws Exception
     */
    public function push()
    {
        $this->beforeTest(false);
        try {
            $this->CI->rabbitmq_client->push('test', json_encode(array('id' => 3, 'name' => "initial data 3")), false, array('delivery_mode' => 2));
            $this->unitTest(1, 1);
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull with exception
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @throws Exception
     */
    public function pullWithException()
    {
        $this->beforeTest(true);
        try {
            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $test = 2;
                    $msg = json_decode($message->body);
                    $this->unitTest($msg->id, 1);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                    throw new Exception('Exception Callback');
                } catch (Exception $e) {
                    $test = 1;
                }
                $this->unitTest($test, 1);
            }, array('delivery_mode' => 2));
            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $msg = json_decode($message->body);
                    $this->unitTest($msg->id, 2);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                } catch (Exception $e) {
                    $this->unitTest(2, 1);
                }
            });
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull with lock
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @throws Exception
     */
    public function pullWithLock()
    {
        $this->beforeTest(true);
        try {
            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $msg = json_decode($message->body);
                    $this->unitTest(1, $msg->id);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                    $this->CI->rabbitmq_client->lock($message);
                } catch (Exception $e) {
                    $this->unitTest(2, 1);
                }
            });
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull with unlock
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @throws Exception
     */
    public function pullWithAck()
    {
        $this->beforeTest(true);
        try {
            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $msg = json_decode($message->body);
                    $this->unitTest(1, $msg->id);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                    $this->CI->rabbitmq_client->unlock($message);
                } catch (Exception $e) {
                    $this->unitTest(2, 1);
                }
            });

            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $msg = json_decode($message->body);
                    $this->unitTest(2, $msg->id);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                    $this->CI->rabbitmq_client->unlock($message);
                } catch (Exception $e) {
                    $this->unitTest(2, 1);
                }
            });
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull without lock and unlock
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @throws Exception
     */
    public function pull()
    {
        $this->beforeTest(true);
        try {
            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $msg = json_decode($message->body);
                    $this->unitTest($msg->id, 1);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                } catch (Exception $e) {
                    $this->unitTest(2, 1);
                }
            });

            $this->CI->rabbitmq_client->pull('test', false, function ($message) {
                try {
                    $msg = json_decode($message->body);
                    $this->unitTest($msg->id, 2);
                    $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
                } catch (Exception $e) {
                    $this->unitTest(2, 1);
                }
            });
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test to purge queue
     *
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     *
     * @throws Exception
     */
    public function purge()
    {
        $this->beforeTest(true);
        try {
            $this->CI->rabbitmq_client->purge();
            $this->unitTest(1, 1);
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }
}