<?php
require('vendor/autoload.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

$url_str = getenv('CLOUDAMQP_URL');

$url = parse_url($url_str);
$vhost = substr($url['path'], 1);

if($url['scheme'] === "amqps") {
    $ssl_opts = array(
        'capath' => 'i:/project/php/php-amqplib-example/cert/',
        'verify_peer' => FALSE,
        'verify_peer_name' => FALSE
    );
    $connection = new AMQPSSLConnection($url['host'], 5671, $url['user'], $url['pass'], $vhost, $ssl_opts);
} else {
    $connection = new AMQPStreamConnection($url['host'], 5672, $url['user'], $url['pass'], $vhost);
}

$channel = $connection->channel();

$channel->exchange_declare('test_exchange', 'direct', false, false, false);
$channel->queue_declare('test_queue', false, false, false, false);
$channel->queue_bind('test_queue', 'test_exchange', 'test_key');

$msg = new AMQPMessage('Hello World!');
$channel->basic_publish($msg, 'test_exchange', 'test_key');

echo " [x] Sent 'Hello World!' to test_exchange / test_queue.\n";

$channel->close();
$connection->close();
?>