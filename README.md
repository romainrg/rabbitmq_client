# rabbitmq_client (CodeIgniter)
CodeIgniter Library used to easilly interract with RabbitMQ 🐰❤

## :books: Dependencies

- PHP 5.4+ (with Composer)
- Rabbit MQ Installed on your server (at least 3.5.*)
- [php-amqplib](https://github.com/videlalvaro/php-amqplib)
- CodeIgniter Framework (3.1.8+ recommanded)

## :beginner: Installation

### :arrow_right: Step 1 : Library installation by Composer

Just by running following command in the folder of your project :
```sh
composer require romainrg/rabbitmq_client
```
Or by adding following lines to your `composer.json` file :
```json
"require": {
    "romainrg/rabbitmq_client": "^4.0.0"
},
```
Don't forget to include your autoload to CI config file :
```php
$config['composer_autoload'] = FCPATH.'vendor/autoload.php';
```

### :arrow_right: Step 2 : Run a composer update in the directory of your project with the following command :

```sh
$ composer require romainrg/rabbitmq_client
```

### :arrow_right: Step 3 : Create the following config file

You have to create it in the CI config folder located in `./application/config/rabbitmq.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Config for Rabbit MQ Library
 */
$config['rabbitmq'] = array(
    'host' => 'localhost',    // <- Your Host     (default: localhost)
    'port' => 5672,           // <- Your Port     (default: 5672)
    'user' => 'username',     // <- Your User     (default: guest)
    'pass' => 'password',     // <- Your Password (default: guest)
    'vhost' => '/'            // <- Your Vhost    (default: /)
);
```

### :arrow_right: Step 4 : Load the library in your CI Core Controller file

(Or just in a CI Controller)

```php
$this->load->add_package_path(APPPATH . 'third_party/rabbitmq');
$this->load->library('rabbitmq');
$this->load->remove_package_path(APPPATH . 'third_party/rabbitmq');
```

### :arrow_right: Step 5 : Enjoy and give me some improvements or ideas ! ;)

## Examples

### :arrow_right: Pushing some datas in a Queue:

This will create, if it does not exist, the **'hello_queue'** queue and insert **'Hello World !'** text inside it.

```php
$this->rabbitmq->push('hello_queue', 'Hello World !');
```

If you want to run your CI Controller Method with CLI command :

```sh
$ php www.mywebsite.com/index.php 'controller' 'method'
```

*You will have the following return*

```sh
$ [+] Pushing 'Hello World !' to 'hello_queue' -> OK
```

### :arrow_right: Fetching some datas from a Queue **(only in CLI at this time)**:

This will fetch last inserted datas from the **'hello_queue'** in real time, with parmanent mode activated and **'_process'** callback function.

The PHP Code :
```php
return $this->rabbitmq->pull('hello_queue', true, array($this, '_process'));
```

Run it in CLI :
```sh
$ php www.mywebsite.com/index.php 'controller' 'method'
```

### :arrow_right: Pushing some datas in a Queue with additional parameters:

This will create, if it does not exist, the **'hello_queue'** queue and insert **'Hello World !'** text inside it, the third parameter **TRUE** set the durability of the **'hello_queue'** (TRUE = permanent, FALSE = not permanent), the last parameter **'delivery_mode (2)'** makes message persistent (you can also add some  parameters to this array).

```php
$this->rabbitmq->push('hello_queue', 'Hello World !', TRUE, array('delivery_mode' => 2));
```
## For more CodeIgniter libraries, give me a :beer::grin:
[> Beer road](https://www.paypal.me/romaingallien)

## :lock: License
[GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html)
