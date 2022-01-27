<?php

namespace App\Controllers;

use App\Database\Pagination;
use App\Http\Request;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Utils\ControllerApi;

class UsersController extends ControllerApi {

  
  private UserRepository $repository;

  public function __construct() {
    $this->repository = new UserRepository;
  }

  public function find(Request $request)
  {
    $queryParams = $request->getQueryParams();
    
    $currentPage  = $queryParams['page'] ?? 1;
    $size         = $queryParams['size'] ?? 10;
    $results      = $this->repository->count(); 

    $pagination = new Pagination($results, $currentPage, $size);
    $users = $this->repository->find(null, null, $pagination);

    return $request->json()->ok([
      'users' => $users
    ]);
  }

  public function findOne(Request $request)
  {
  }

  public function create(Request $request)
  {
    $postVars = $request->getPostVars();

    if(!$postVars['name'] || !$postVars['age']) {
      return $request->json()->conflict("Informe os parâmetros 'name' e 'age'");
    }

    $user = new User($postVars['name'], $postVars['age']);
    $this->repository->create($user);

    if(!($user instanceof User)) 
      return $request->json()->conflict("Error create new user.");

    return $request->json()->created([
      'user' => $user,
    ]);
  }
  
  public function update(Request $request, string $id)
  {
    $postVars = $request->getPostVars();

    $user = new User($postVars['name'], $postVars['age']);
    $this->repository->update($user, $id);
    
    return $request->json()->ok([
      'user' => $user,
    ]);
  }

  public function delete(Request $request, int $id)
  {
    $this->repository->destroy($id);

    return $request->json()->ok(['message' => 'Usúario excluído com sucesso!']);
  }
}