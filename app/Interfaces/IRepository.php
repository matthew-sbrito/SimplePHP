<?php

namespace App\Interfaces;

use App\Database\Pagination;

interface IRepository {
  public function find($where, $order,Pagination $limit, $fields);
  public function count(): ?int;
  public function findOne($id, $field);
  public function create($model);
  public function update($model, $id, $field);
  public function destroy($id, $field);
  public function start(): self;
  public function commit(): void;
  public function rollback(): void;
}