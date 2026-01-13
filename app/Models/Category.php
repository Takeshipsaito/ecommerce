<?php 

namespace App\Models;

use App\DB\Sql; // <--- Isso garante que ele use o arquivo da pasta DB que acabamos de criar

class Category {

  public static function listAll() {
    $sql = new Sql();
    
    // Fazemos um JOIN para buscar o nome que está na tabela tb_persons
    return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
}
}
?>