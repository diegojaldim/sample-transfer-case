<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$router->group(['prefix' => '/v1'], function () use ($router) {
    $router->get('/', function () use ($router) {
        return 'Api v1 on!';
    });

    $router->group(['prefix' => '/transaction'], function () use ($router) {
        $router->post('/', 'TransactionController@make');
    });
});
