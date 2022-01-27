<?php

namespace App\Interfaces;

use App\Database\Pagination;

interface IRepository {
  public function find($where, $order,Pagination $limit, $fields);
  public function count();
  public function findOne($id);
  public function create($model);
  public function update($model, $id);
  public function destroy($id);
}