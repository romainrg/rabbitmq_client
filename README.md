# CodeIgniter Rabbit MQ Library

> CodeIgniter Library for RabbitMQ interactions with CodeIgniter using PHP-AMQPLib

## Dependencies

- Rabbit MQ Installed on your server (at least 3.5.*)
- [php-amqplib](https://github.com/videlalvaro/php-amqplib)
- CodeIgniter Framework (3.0.* recommanded)
- PHP 5.4+ (with Composer)

## Installation

### Step 1 : Add the following line to your composer.json file

```json
"require": {
    "romainrg/codeigniter-rabbitmq-library": "1.0.*"
},
```

### Step 2 : Run a composer update in the directory of your project with the following command :

```sh
$ composer require romainrg/codeigniter-rabbitmq-library
```

### Step 3 : Create the following config file

You have to create it in the CI config folder located in `./application/config/rabbitmq.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . '/third_party/rabbitmq/config/rabbitmq.php');
```

### Step 4 : Edit the config file

The config file is located in the directory `./application/third_party/rabbimq/config/rabbitmq.php`

```php
/**
 * Config for Rabbit MQ Library
 */
$config['rabbitmq'] = array(
    'host' => 'localhost',    <- Your Host (default: localhost)
    'port' => 5672,           <- Your Port (default)
    'user' => 'username',     <- Your User (default: guest)
    'pass' => 'password'      <- Your Password (default: guest)
);
```

### Step 5 : Load the library in your CI Core Controller file

(Or just in a CI Controller)

```php
$this->load->add_package_path(APPPATH . 'third_party/rabbitmq');
$this->load->library('rabbit_mq');
$this->load->remove_package_path(APPPATH . 'third_party/rabbitmq');
```

### Step 6 : Enjoy ;)

## Examples

#### 1 - Pushing some datas in a Queue:

This will create, if it does not exist, the **'hello_queue'** queue and insert **'Hello World !'** text inside it.

```php
$this->rabbit_mq->push('hello_queue', 'Hello World !');
```

#### 2 - Fetching some datas from a Queue:

This will fetch last inserted datas from the **'hello_queue'** with limit of **100** results.

```php
$this->rabbit_mq->pull('hello_queue', 100);
```

## License

[MIT License](http://opensource.org/licenses/MIT)
