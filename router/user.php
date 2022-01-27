<?php

use \App\Controllers\UsersController;

//ROTA SOBRE
$router->middlewares(['jwtAuth' ])->get('/api/users',[
    fn($request) => (new UsersController)->find($request),
]);

$router->middlewares(['jwtAuth' ])->get('/api/users/{id}',[
    fn($request, $id) => (new UsersController)->findOne($request, $id)
]);

$router->middlewares(['jwtAuth' ])->post('/api/users',[
    fn($request) => (new UsersController)->create($request)
]);

$router->middlewares(['jwtAuth' ])->put('/api/users/{id}',[
    fn($request, $id) => (new UsersController)->update($request, $id)
]);

$router->middlewares(['jwtAuth' ])->delete('/api/users/{id}',[
    fn($request, $id) => (new UsersController)->delete($request, $id)
]);