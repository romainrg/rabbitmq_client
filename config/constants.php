<?php
defined('BASEPATH') OR exit('No direct script access allowed');

defined('RABBITMQ_CLIENT_PATH') OR define('RABBITMQ_CLIENT_PATH', FCPATH . 'vendor/romainrg/rabbitmq_client');
defined('SOCKET_EAGAIN') or define('SOCKET_EAGAIN', false);
