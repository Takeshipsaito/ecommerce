<?php 

namespace App\Models;

use App\DB\Sql; // <--- Isso garante que ele use o arquivo da pasta DB que acabamos de criar
use Hcode\Model;

class User {

    private $values = [];

    public function setData($data) {
        foreach ($data as $key => $value) {
            $this->values[$key] = $value;
        }
    }

    public function getValues() {
        return $this->values;
    }

    public function __call($name, $args) {
        $method = substr($name, 0, 3);
        $fieldName = strtolower(substr($name, 3));

        if ($method == "get") {
            return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
        }
    }

    public function get($iduser) {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE iduser = :iduser", [
            ":iduser" => $iduser
        ]);

        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function update() {
    $sql = new \App\DB\Sql();

    // Tratando o telefone vazio para evitar o erro 1366
    $phone = $this->getnrphone();
    if (!$phone || $phone == "") {
        $phone = 0; 
    }

    $sql->select("CALL sp_users_update(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", [
        ":iduser"      => (int)$this->getiduser(),
        ":desperson"   => $this->getdesperson(),
        ":deslogin"    => $this->getdeslogin(),
        ":despassword" => password_hash($this->getdespassword(), PASSWORD_BCRYPT),
        ":desemail"    => $this->getdesemail(),
        ":nrphone"     => $phone, // Enviando o valor tratado
        ":inadmin"     => (int)$this->getinadmin()
    ]);
}

  public static function listAll() {
    $sql = new Sql();
    
    // Fazemos um JOIN para buscar o nome que está na tabela tb_persons
    return $sql->select("
        SELECT * FROM tb_users a 
        INNER JOIN tb_persons b ON a.idperson = b.idperson 
        ORDER BY b.desperson
    ");
}
public function save() {
    $sql = new \App\DB\Sql();

    // Tratando o telefone vazio antes de enviar para o banco
    $phone = $this->getnrphone();
    if (!$phone || $phone == "") {
        $phone = 0; 
    }

    $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", [
        ":desperson"   => $this->getdesperson(),
        ":deslogin"    => $this->getdeslogin(),
        ":despassword" => password_hash($this->getdespassword(), PASSWORD_BCRYPT),
        ":desemail"    => $this->getdesemail(),
        ":nrphone"     => $phone, // Variável tratada
        ":inadmin"     => $this->getinadmin()
    ]);

    if (count($results) > 0) {
        $this->setData($results[0]);
    }
}
public function delete() {
    $sql = new \App\DB\Sql();
    // Primeiro pegamos o idperson para limpar a tb_persons depois
    $idperson = $this->getidperson();

    // Deleta o usuário (a tb_users geralmente tem FK com a tb_persons)
    $sql->rawQuery("DELETE FROM tb_users WHERE iduser = :iduser", [
        ":iduser" => $this->getiduser()
    ]);

    // Deleta a pessoa
    $sql->rawQuery("DELETE FROM tb_persons WHERE idperson = :idperson", [
        ":idperson" => $idperson
    ]);
}

public static function getForgot(string $email)
{
    $sql = new Sql();

    $results = $sql->select("
        SELECT *
        FROM tb_users a
        INNER JOIN tb_persons b ON a.idperson = b.idperson
        WHERE b.desemail = :email
    ", [
        ':email' => $email
    ]);

    if (count($results) === 0) {
        throw new \Exception("Não foi possível recuperar a senha.");
    }

    $data = $results[0];

    // Gera token seguro
    $token = bin2hex(openssl_random_pseudo_bytes(32));

    // Salva pedido de recuperação
    $sql->rawQuery("
        INSERT INTO tb_userspasswordsrecoveries
        (iduser, desip, dtrecovery)
        VALUES (:iduser, :ip, DATE_ADD(NOW(), INTERVAL 1 HOUR))
    ", [
        ':iduser' => $data['iduser'],
        ':ip'     => $_SERVER['REMOTE_ADDR']
    ]);

    // 🔜 próximo passo do curso: enviar email
}

}

