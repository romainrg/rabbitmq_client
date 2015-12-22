<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Config for Rabbit MQ Library
 */
$config['rabbitmq'] = array(
    'host' => 'localhost',
    'port' => 5672,
    'user' => 'username',
    'pass' => 'password',
    'vhost' => '/'
);