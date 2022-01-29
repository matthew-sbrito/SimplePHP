<?php

namespace App\Database;

use \PDO;
use \PDOException;

class Database{

  /**
   * Driver do banco de dados
   * @var string
   */
  private static $driver;

  /**
   * Host de conexão com o banco de dados
   * @var string
   */
  private static $host;

  /**
   * Nome do banco de dados
   * @var string
   */
  private static $name;

  /**
   * Usuário do banco
   * @var string
   */
  private static $user;

  /**
   * Senha de acesso ao banco de dados
   * @var string
   */
  private static $pass;

  /**
   * Porta de acesso ao banco
   * @var integer
   */
  private static $port;

  /**
   * Nome da tabela a ser manipulada
   * @var string
   */
  private $table;

  /**
   * Instancia de conexão com o banco de dados
   * @var PDO
   */
  private $connection;

  /**
   * Método responsável por configurar a classe
   * @param  string  $host
   * @param  string  $name
   * @param  string  $user
   * @param  string  $pass
   * @param  integer $port
   */
  public static function config($config){
    self::$driver = $config['driver'];
    self::$host   = $config['host'];
    self::$name   = $config['name'];
    self::$user   = $config['user'];
    self::$pass   = $config['pass'];
    self::$port   = $config['port'];
  }

  /**
   * Define a tabela e instancia e conexão
   * @param string $table
   */
  public function __construct($table = null){
    $this->table = $table;
    $this->setConnection();
  }

  /**
   * Método responsável por criar uma conexão com o banco de dados
   */
  private function setConnection(){
    try{
      $config = self::$driver.':dbname='.self::$host.self::$name.';charset=utf8;dialect=3';
     
      $this->connection = new PDO( $config, self::$user, self::$pass );
      $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
      die('ERROR: '.$e->getMessage());
    }
  }

  public function beginTransaction(){
    $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
    $this->connection->beginTransaction();
  }

  public function commit(){
    $this->connection->commit();
  }

  public function rollback(){
    $this->connection->rollback();
  }


  /**
   * Método responsável por executar queries dentro do banco de dados
   * @param  string $query
   * @param  array  $params
   * @return PDOStatement
   */
  public function execute($query,$params = []){
    try{
      $statement = $this->connection->prepare($query);
      $statement->execute($params);
      return $statement;
    }catch(PDOException $e){
      switch($e->getCode()) {
        case 23000:
          throw new \Exception('Database Error: Dados já existentes!');
        default:
          throw new \Exception('Database Error: '.$e->getMessage());
      }
    }
  }

  /**
   * Método responsável por inserir dados no banco
   * @param  array $values [ field => value ]
   * @return integer ID inserido
   */
  public function insert($values){
    $values = self::objectToArray($values);
    
    //DADOS DA QUERY
    $fields = array_keys($values);
    $binds  = array_pad([],count($fields),'?');

    //MONTA A QUERY
    $query = 'INSERT INTO '.$this->table.' ('.implode(',',$fields).') VALUES ('.implode(',',$binds).')';

    //EXECUTA O INSERT
    $result = $this->execute($query,array_values($values));

    // RETORNA O ID  
    return $this->connection->lastInsertId();

  }

  /**
   * Método responsável por executar uma consulta no banco
   * @param  string $where
   * @param  string $order
   * @param  string $limit
   * @param  string $fields
   * @return PDOStatement
   */   
  public function select($where = null, $order = null, ?Pagination $pagination = null, $fields = '*'){
    
    //DADOS DA QUERY
    $where = strlen($where) ? 'WHERE '.$where : '';
    $order = strlen($order) ? 'ORDER BY '.$order : '';
    $limit = $pagination    ? $this->getLimit($pagination) : ''; 

    //MONTA A QUERY
    $query = 'SELECT '.$fields.' FROM '.$this->table.' '.$where.' '.$order.' '.$limit;
    // return $query;
    
    //EXECUTA A QUERY
    return $this->execute($query);
  }

  private function getLimit(Pagination $pagination) {
    return $pagination->getLimit();
  }

  /**
   * Método responsável por executar atualizações no banco de dados
   * @param  string $where
   * @param  array $values [ field => value ]
   * @return boolean
   */
  public function update($where,$values){
    $values = self::objectToArray($values);

    //DADOS DA QUERY
    $fields = array_keys($values);

    //MONTA A QUERY
    $query = 'UPDATE '.$this->table.' SET '.implode('=?,',$fields).'=? WHERE '.$where;
    // return $query;

    //EXECUTAR A QUERY
    $this->execute($query,array_values($values));

    //RETORNA SUCESSO
    return true;
  }

  /**
   * Método responsável por excluir dados do banco
   * @param  string $where
   * @return boolean
   */
  public function delete($where){
    //MONTA A QUERY
    $query = 'DELETE FROM '.$this->table.' WHERE '.$where;

    //EXECUTA A QUERY
    $this->execute($query);

    //RETORNA SUCESSO
    return true;
  }

  private static function objectToArray($object){
    if(!is_object($object)) return $object;
    foreach($object as $key => $value){
      $array[$key] = $value;
    }
    return $array;
  }

}