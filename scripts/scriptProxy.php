<?php

$path = getcwd();
if (!is_file($path . '/vendor/autoload.php')) {
    $path = dirname(getcwd());
}
chdir($path);

require $path . '/vendor/autoload.php';

use zaboy\async\Callback\CallbackException;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Client as Promise;
use \zaboy\async\Promise\Factory\StoreFactory;

$options = $_SERVER['argv'];
array_shift($options);
$options = unserialize(base64_decode(array_shift($options)));

/** @var Zend\ServiceManager\ServiceManager $container */
$container = include './config/container.php';

/** @var Store $store */
$store = $container->get(StoreFactory::KEY);
$promise = new Promise($store, $options['promiseId']);
unset($options['promiseId']);

try {
    if (is_callable($options['callback'])) {
        $result = call_user_func($options['callback'], $options['value'], $promise);
    } else {
        throw new CallbackException('Specified callback "' . print_r($options['callback'], 1) . '" is not callable');
    }
    $promise->resolve($result);
} catch (\Exception $e) {
    $promise->reject($e);
}

