<?php

require_once __DIR__.'/../vendor/autoload.php';

// Загружаем настройки из .env файла
$config = (new Dotenv\Dotenv(__DIR__.'/../'))->load();

// Создаем экземпляр приложения
$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();

// Роуты
$app->router->group([
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'api/v1'
], function ($router) {
    $router->get('/rates', 'CoinController@getRates');
});

return $app;
