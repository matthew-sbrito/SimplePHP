<?php

namespace App\Http;

use App\Exceptions\ResponseException;

class Request{

    /** 
     *  Método HTTP da requisição
     * @var string
     */
    private $httpMethod;

    /**
     *  URI da página
     * @var string
     */
    private $uri;
    
    private $contentType;
    
    /**
     *  Parâmetros da URL ($_GET)
     * @var array
     */
    private $queryParams = [];

    /**
     *  Váriaveis recebidas no Método POST da página ($_POST)
     * @var array
     */
    private $postVars = [];

     /**
     *  Cabeçalho da requisição
     * @var array
     */
    private $headers = [];

    /**
     *  Router da página
     * @var Router
     */
    private $router = [];

    /**
     *  Construtor da classe 
     */
    public function __construct($router){
        $this->router       = $router;
        $this->queryParams  = $_GET ?? [];
        $this->headers      = getallheaders();
        $this->httpMethod   = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->setUri();
        $this->setPostVars();
    }
    
    private function setPostVars(){
        if($this->httpMethod == 'GET') return false;
        
        $jsonArray = json_decode(file_get_contents('php://input'), true);
        
        $this->postVars = array_merge($jsonArray, $_POST) ?? [];
        $this->sanitize();
    }

    private function setUri(){
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $xUri = explode('?', $uri); 
        $this->uri = $xUri[0];
    }

    public function download(): self {
        $this->contentType = "application/oct-stream";
        $this->getRouter()->setContentType($this->contentType);
        return $this;
    }

    public function json(): self {
        $this->contentType = "application/json";
        $this->getRouter()->setContentType($this->contentType);
        return $this;
    }
    
    public function pdf(): self {
        $this->contentType = "application/pdf";
        $this->getRouter()->setContentType($this->contentType);
        return $this;
    }
    
    public function html(): self {
        $this->contentType = "text/html";
        $this->getRouter()->setContentType($this->contentType);
        return $this;
    }
    
    public function xml(): self {
        $this->contentType = "text/xml";
        $this->getRouter()->setContentType($this->contentType);
        return $this;
    }

    public function httpResponse(int $code, $content) {
        if($code >= 400){
            throw new ResponseException($content, $code);
        }
        return new Response($code, $content, $this->contentType);
    }

    public function ok($content){
        return $this->httpResponse(200, $content);
    }

    public function created($content){
        return $this->httpResponse(201, $content);
    }
 
    public function noContent($content){
        return $this->httpResponse(204, $content);
    }
    
    public function unauthorized(string $message = ""){
        return $this->httpResponse(401, $message);
    }
    
    public function forbidden(string $message = ""){
        return $this->httpResponse(403, $message);
    }

    public function notFound(string $message = ""){
        return $this->httpResponse(404, $message);
    }
    public function conflict(string $message = ""){
        return $this->httpResponse(409, $message);
    }
    public function serverError(string $message = ""){
        return $this->httpResponse(500, $message);
    }

    /** 
     *  Método responsável por retornar o router
     *   @return Router
     */
    public function getRouter(){
        return $this->router;
    }
    /** 
     *  Método responsável por retornar o metodo HTTP da requisição
     *   @return string
     */
    public function getHttpMethod(){
        return $this->httpMethod;
    }

    /** 
     *  Método responsável por retornar a uri da requisição
     *   @return string
     */
    public function getUri(){
        return $this->uri;
    }

    /** 
     *  Método responsável por retornar os Headers da requisição
     *   @return string
     */
    public function getHeaders(){
        return $this->headers;
    }

     /** 
     *  Método responsável por retornar os parâmetros da URL($_GET) da requisição
     *   @return array
     */
    public function getQueryParams(){
        return $this->queryParams;
    }

      /** 
     *  Método responsável por retornar os parãmetros POST($_POST) da requisição
     *   @return array
     */
    public function getPostVars(): array{
        return $this->postVars;
    }

    /**
     * Método responsável por limpar os dados do post, evitando injection, e deixando todos em UPPER
     * @return object
     */
    private function sanitize() {
        foreach($this->postVars as $key => $value) {  
            $cleanValue = $value;
            if(isset($cleanValue)) {
                $cleanValue = strip_tags(trim($cleanValue));
                $cleanValue = htmlentities($cleanValue, ENT_NOQUOTES);
                $cleanValue = html_entity_decode($cleanValue, ENT_NOQUOTES, 'UTF-8');
            }
            unset($this->postVars[$key]);
            $this->postVars[$key] = $cleanValue;
        }
    }
}