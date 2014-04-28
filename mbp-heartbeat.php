<?php
/**
 * mbp-heartbeat.php
 *
 * A producer to create automated test entries in the Message Broker system exchanges. The results of the test entries will be consumed, monitored and produce an automated monitoring report (mbc-heartbeat).
 */

// Load up the Composer autoload magic
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration settings common to the Message Broker system
// symlinks in the project directory point to the actual location of the files
require __DIR__ . '/mb-secure-config.inc';
require __DIR__ . '/mb-config.inc';

// Settings
$credentials = array(
  'host' =>  getenv("RABBITMQ_HOST"),
  'port' => getenv("RABBITMQ_PORT"),
  'username' => getenv("RABBITMQ_USERNAME"),
  'password' => getenv("RABBITMQ_PASSWORD"),
  'vhost' => getenv("RABBITMQ_VHOST"),
);

$config = array(
  'exchange' => array(
    'name' => getenv("MB_TRANSACTIONAL_EXCHANGE"),
    'type' => getenv("MB_TRANSACTIONAL_EXCHANGE_TYPE"),
    'passive' => getenv("MB_TRANSACTIONAL_EXCHANGE_PASSIVE"),
    'durable' => getenv("MB_TRANSACTIONAL_EXCHANGE_DURABLE"),
    'auto_delete' => getenv("MB_TRANSACTIONAL_EXCHANGE_AUTO_DELETE"),
  ),
  'queue' => array(
    array(
      'name' => getenv("MB_USER_REGISTRATION_QUEUE"),
      'passive' => getenv("MB_USER_REGISTRATION_QUEUE_PASSIVE"),
      'durable' => getenv("MB_USER_REGISTRATION_QUEUE_DURABLE"),
      'exclusive' => getenv("MB_USER_REGISTRATION_QUEUE_EXCLUSIVE"),
      'auto_delete' => getenv("MB_USER_REGISTRATION_QUEUE_AUTO_DELETE"),
      'bindingKey' => getenv("MB_USER_REGISTRATION_QUEUE_TOPIC_MB_TRANSACTIONAL_EXCHANGE_PATTERN"),
    ),
    array(
      'name' => getenv("MB_USER_API_REGISTRATION_QUEUE"),
      'passive' => getenv("MB_USER_API_REGISTRATION_QUEUE_PASSIVE"),
      'durable' => getenv("MB_USER_API_REGISTRATION_QUEUE_DURABLE"),
      'exclusive' => getenv("MB_USER_API_REGISTRATION_QUEUE_EXCLUSIVE"),
      'auto_delete' => getenv("MB_USER_API_REGISTRATION_QUEUE_AUTO_DELETE"),
      'bindingKey' => getenv("MB_USER_API_REGISTRATION_QUEUE_TOPIC_MB_TRANSACTIONAL_EXCHANGE_PATTERN"),
    ),
  ),
);

// Test parameters typical of what is produced by
// message_broker_producer.module.
$param = array(
  'activity' => 'user_register',
  'email' => 'dlee+messagebroker-heartbeat-test-user-register-' . rand(1, 99) . '@dosomething.org',
  'uid' => '666',
  'birthdate' => mktime(0, 0, 0, 6, 13, 1999), // 13 June 1999 (over 13)
  'merge_vars' => array(
    'FNAME' => 'Heartbeat-First-Name',
  ),
  'activity_timestamp' => time(),
  'application_id' => 0
);

try {
  $config['routingKey'] = 'user.registration.test';
  $messageBroker = new MessageBroker($credentials, $config);
  $message = serialize($param);
  $messageBroker->publishMessage($message);
}
catch (Exception $e) {
  trigger_error('mbp-heartbeat ERROR - Failed to produce user.registration.test', E_USER_WARNING);
}