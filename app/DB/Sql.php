<?php 

namespace App\DB;

use \PDO;

class Sql extends PDO {

    public function __construct() {
        // Usamos parent para conectar direto na classe pai (PDO)
        parent::__construct("mysql:host=127.0.0.1;dbname=db_ecommerce", "root", "a");
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function setParams($statement, $parameters = array()) {
        foreach ($parameters as $key => $value) {
            $this->setParam($statement, $key, $value);
        }
    }

    private function setParam($statement, $key, $value) {
        $statement->bindParam($key, $value);
    }

    // Usamos rawQuery para evitar conflito com o PDO nativo
    public function rawQuery($rawQuery, $params = array()) {
        $stmt = $this->prepare($rawQuery);
        $this->setParams($stmt, $params);
        $stmt->execute();
        return $stmt;
    }

    public function select($rawQuery, $params = array()):array {
        $stmt = $this->rawQuery($rawQuery, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}