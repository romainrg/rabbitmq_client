<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Test Rabbitmq_client
 * @package  Rabbitmq_client
 * @category Tests
 * @version  20180320
 */
class Test_Rabbitmq_client extends TestCase
{
    /**
     * Dynamic message id to compare with result
     * @var int
     */
    public static $idMessage = 10;

    /**
     * Constructor
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function __construct()
    {
        $this->activateTest = true;
        $this->stressTest = false;
        parent::__construct();

        $this->CI->load->add_package_path(RABBITMQ_PATH)
            ->library('rabbitmq_client')
            ->remove_package_path(RABBITMQ_PATH);
    }

    /**
     * Test push to rabbitmq
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function push()
    {
        try {
            $this->CI->rabbitmq_client->push('test', json_encode(array('id' => 10, 'name' => "testlock")), false, array('delivery_mode' => 2));
            $this->CI->rabbitmq_client->push('test', json_encode(array('id' => 11, 'name' => "testunlock")), false, array('delivery_mode' => 2));
            $this->CI->rabbitmq_client->push('test', json_encode(array('id' => 12, 'name' => "test")), false, array('delivery_mode' => 2));
            $this->unitTest(1, 1);
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull with lock
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function pullWithLock()
    {
        try {
            $this->CI->rabbitmq_client->pull('test', false, array($this, "notest_pull_withoutack_callback"));
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull with unlock
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function pullWithAck()
    {
        try {
            $this->CI->rabbitmq_client->pull('test', false, array($this, "notest_pull_withack_callback"));
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Test pull without lock and unlock
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function pull()
    {
        try {
            $this->CI->rabbitmq_client->pull('test', false, array($this, "notest_pull_callback"));
            $this->CI->rabbitmq_client->pull('test', false, array($this, "notest_pull_callback"));
        } catch (Exception $e) {
            $this->unitTest(2, 1);
        }
    }

    /**
     * Callback to pull with lock
     * @param object $message
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function notest_pull_withoutack_callback($message)
    {
        $msg = json_decode($message->body);
        $this->unitTest($msg->id, self::$idMessage);
        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        $this->CI->rabbitmq_client->lock($message);
    }

    /**
     * Callback to pull with unlock
     * @param object $message
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function notest_pull_withack_callback($message)
    {
        $msg = json_decode($message->body);
        $this->unitTest($msg->id, self::$idMessage);
        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        $this->CI->rabbitmq_client->unlock($message);
        self::$idMessage++;
    }

    /**
     * Callback to pull without lock and unlock
     * @param object $message
     * @author Stéphane Lucien-Vauthier <s.lucien_vauthier@santiane.fr>
     */
    public function notest_pull_callback($message)
    {
        $msg = json_decode($message->body);
        $this->unitTest($msg->id, self::$idMessage);
        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        self::$idMessage++;
    }
}