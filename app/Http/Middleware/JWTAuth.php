<?php 

namespace App\Http\Middleware;

use App\Http\Request;
use App\Repositories\UserRepository;
use App\Models\User;
use Closure;
use Firebase\JWT\JWT;

class JWTAuth {

  /**
   * Método resposável por executar o middleware
   * @param   Request  
   * @param   Closure  next
   * @return  Response 
   */
  public function handle(Request $request, Closure $next){
    $this->auth($request);
    
    return $next($request);
  }

  private function auth(Request $request){
    if($user = $this->getJWTAuthUser($request)){
      $request->user = $user;
      return true;
    }
    $request->forbidden('Invalid token, Unauthorized');
  }

  private function getJWTAuthUser(Request $request){
    
    $headers = $request->getHeaders();
    
    $jwt = isset($headers['Authorization']) ? str_replace('Bearer ','',$headers['Authorization']) : '';
    
    try {
      $decoded = (array)JWT::decode($jwt, getenv('JWT_KEY'),['HS256']);
    } catch (\Exception $e) {
      $request->unauthorized('Invalid token, Unauthorized');
    }

    $user = (new UserRepository)->findByEmail($decoded['EMAIL']);
    
    if(!$user instanceof User) throw new \Exception("Invalid token, Unauthorized",400);
  
    if(!password_verify($decoded['PASSWORD'], $user->SENHA)) {
      $request->unauthorized('Invalid token, Unauthorized');
    }
    return $user;
  }
}