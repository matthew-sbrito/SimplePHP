<?php

namespace App\Utils;

use App\Database\Pagination;

class ControllerApi {
  public static function getPagination(Pagination $pagination): array{
    return [
      [
        'currentPage' => (int)$pagination->getCurrentPage(),
        'totalPages'  => (int)$pagination->getTotalPage(),
        'items'       => (int)$pagination->getCountResults(),
        'offset'      => $pagination->getLimit(),
      ]
    ];
  }
  public static function getFilterByParams(array $queryParams): ?string{
    unset($queryParams['page']); 
    foreach($queryParams as $key => $value){
        $conditions[] = $key . ' LIKE "%'. str_replace(' ','%',$value).'%"';
    }

    return implode(' AND ', $conditions);
}
}