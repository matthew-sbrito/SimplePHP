<?php

namespace App\Repositories;

use App\Interfaces\IRepository;
use App\Database\Database;
use App\Database\Pagination;

abstract class Repository implements IRepository
{

  /**
   * Instância do banco de dados para realização do CRUD.
   * @var Database
   */
  protected $database;

  /**
   * Chave primária da tabela da instância atual.
   * @var string
   */
  protected $id;

  /**
   * Váriavel responsável por guardar class do model atual.
   * @var class
   */
  protected $model;

  public function find($where = null, $order = null, ?Pagination $limit = null, $fields = '*')
  {
    $statement = $this->database->select($where, $order, $limit, $fields);

    if (!$this->model) return $statement->fetchAll(\PDO::FETCH_ASSOC);

    while ($entity = $statement->fetchObject($this->model)) {
      $entities[] = $entity;
    }

    return $entities;
  }

  public function count($where = null): ?int
  {
    return $this->database->select($where, null, null, 'count(*) as QTD')
      ->fetchObject()->QTD;
  }

  public function findOne($id, $field = null)
  {    
    $field = strlen($field) ? $field : $this->id;
    $where = "$field = $id";    
    
    $statement = $this->database->select($where);

    if (!$this->model) return $statement->fetch(\PDO::FETCH_ASSOC);

    return $statement->fetchObject($this->model);
  }

  public function create($model)
  {
    $id = $this->database->insert($model, $this->id);
    return $this->findOne($id);
  }

  public function update($model, $id, $field = null)
  {
    $field = strlen($field) ? $field : $this->id;
    $where = "$field = $id"; 

    return $this->database->update($where, $model);
  }

  public function destroy($id, $field = null)
  {
    $field = strlen($field) ? $field : $this->id;
    $where = "$field = $id"; 
    
    return $this->database->delete($where);
  }

  public function start(): self
  {
    $this->database->beginTransaction();
    return $this;
  }

  public function commit(): void
  {
    $this->database->commit();
  }

  public function rollback(): void
  {
    $this->database->rollback();
  }
}
