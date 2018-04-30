<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Config for Rabbit MQ Library
 */
$config['rabbitmq_client'] = array(
    'host' => 'localhost',
    'port' => 5672,
    'user' => 'rabbitmq',
    'pass' => 'rabbitmq',
    'vhost' => '/'
);