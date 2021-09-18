<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

try {
    $rootPath = realpath('..');
    require_once $rootPath . '/vendor/autoload.php';

    /**
     * Load ENV variables
     */
    Dotenv::createImmutable($rootPath)->load();

    /**
     * Init Phalcon Dependency Injection
     */
    $di = new FactoryDefault();
    $di->offsetSet('rootPath', function () use ($rootPath) {
        return $rootPath;
    });

    /**
     * Register Service Providers
     */
    $providers = $rootPath . '/config/providers.php';
    if (!file_exists($providers) || !is_readable($providers)) {
        throw new Exception('File providers.php does not exist or is not readable.');
    }

    /** @var array $providers */
    $providers = include_once $providers;
    foreach ($providers as $provider) {
        $di->register(new $provider());
    }

    /**
     * Init MVC Application and send output to client
     */
    $application = new Phalcon\Mvc\Application($di); // Important: mustn't ignore $di param . The Same Micro APP: new Phalcon\Mvc\Micro($di);
    $di['app'] = $application; //  Important

    // (new \Snowair\Debugbar\ServiceProvider())->start();

    $application
        ->handle($_SERVER['REQUEST_URI'])
        ->send();
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
