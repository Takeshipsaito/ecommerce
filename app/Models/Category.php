<?php
namespace App\Models;

// Usando o caminho completo para não ter erro de interpretação
use Hcode\Model as HcodeModel;
use Hcode\DB\Sql;

class Category extends HcodeModel {

    public static function listAll() {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save() {
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", [
            ":idcategory" => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ]);
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }
}