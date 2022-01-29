<?php

use \App\Controllers\UsersController;
use App\Http\Router;

//ROTA USERS
$router->middlewares(['jwtAuth'])->group("/api/users", function(Router &$router) {
    $usersController = new UsersController;

    $router->get('/',[
        fn($request) => $usersController->find($request),
    ]);
    $router->get('/{id}',[
        fn($request, $id) => $usersController->findOne($request, $id)
    ]);
    $router->post('/',[
        fn($request) => $usersController->create($request)
    ]);
    $router->put('/{id}',[
        fn($request, $id) => $usersController->update($request, $id)
    ]);
    $router->delete('/{id}',[
        fn($request, $id) => $usersController->delete($request, $id)
    ]);
});
