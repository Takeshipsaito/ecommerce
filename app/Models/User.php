<?php

namespace App\Models;

use App\DB\Sql;

class User
{
    private array $data = [];

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getValues(): array
    {
        return $this->data;
    }

    public function __call($name, $args)
    {
        $method = substr($name, 0, 3);
        $field  = strtolower(substr($name, 3));

        if ($method === 'set') {
        $this->data[$field] = $args[0];
        }

    }

    public function loadByEmail(string $email): void
    {
    $sql = new Sql();

    $result = $sql->select("
        SELECT u.*, p.desemail
        FROM tb_users u
        INNER JOIN tb_persons p ON p.idperson = u.idperson
        WHERE p.desemail = :email
        LIMIT 1
    ",[
        ':email'=>$email
    ]);

    if($result){
        $this->setData($result[0]);
    }
    }

    public function loadByToken(string $token): void
    {
    $sql = new Sql();

    $result = $sql->select("
        SELECT *
        FROM tb_users
        WHERE reset_token = :token
        LIMIT 1
    ",[
        ':token'=>$token
    ]);

    if($result){
        $this->setData($result[0]);
    }
    }

    /* ==========================
       LISTAR USUÁRIOS
    ========================== */
    public static function listAll(): array
    {
        $sql = new Sql();

        return $sql->select("
            SELECT 
                u.iduser,
                p.desperson,
                p.desemail,
                u.deslogin,
                u.inadmin,
                u.dtregister
            FROM tb_users u
            INNER JOIN tb_persons p ON p.idperson = u.idperson
            ORDER BY u.iduser ASC
        ");
    }


    /* ==========================
       BUSCAR UM USUÁRIO
    ========================== */
    public function get(int $iduser): void
    {
        $sql = new Sql();

        $result = $sql->select("
            SELECT 
                u.iduser,
                p.desperson,
                p.desemail,
                p.nrphone,
                u.deslogin,
                u.inadmin
            FROM tb_users u
            INNER JOIN tb_persons p ON p.idperson = u.idperson
            WHERE u.iduser = :iduser
        ", [
            ':iduser' => $iduser
        ]);

        if ($result) {
            $this->setData($result[0]);
        }
    }


    /* ==========================
       CRIAR USUÁRIO
    ========================== */
    public function save(): void
    {
        $sql = new Sql();

        // 1️⃣ cria a pessoa
        $sql->execute("
            INSERT INTO tb_persons (desperson, desemail, nrphone)
            VALUES (:desperson, :desemail, :nrphone)
        ", [
            ':desperson' => $this->getdesperson(),
            ':desemail'  => $this->getdesemail(),
            ':nrphone' => (int) preg_replace('/\D/', '', $this->getnrphone())
        ]);

        // 2️⃣ pega o idperson criado
        $idperson = (int)$sql
            ->select("SELECT LAST_INSERT_ID() AS idperson")[0]['idperson'];

        // 3️⃣ cria o usuário
        $sql->execute("
            INSERT INTO tb_users (idperson, deslogin, despassword, inadmin)
            VALUES (:idperson, :deslogin, :despassword, :inadmin)
        ", [
            ':idperson'    => $idperson,
            ':deslogin'    => $this->getdeslogin(),
            ':despassword' => password_hash($this->getdespassword(), PASSWORD_BCRYPT),
            ':inadmin'     => (int)$this->getinadmin()
        ]);
    }


    /* ==========================
       ATUALIZAR USUÁRIO
    ========================== */
    public function update(): void
    {
        $sql = new Sql();

        // atualiza pessoa
        $sql->execute("
            UPDATE tb_persons SET
                desperson = :desperson,
                desemail  = :desemail,
                nrphone   = :nrphone
            WHERE idperson = (
                SELECT idperson FROM tb_users WHERE iduser = :iduser
            )
        ", [
            ':desperson' => $this->getdesperson(),
            ':desemail'  => $this->getdesemail(),
            ':nrphone'   => $this->getnrphone(),
            ':iduser'    => $this->getiduser()
        ]);

        // atualiza usuário
        $sql->execute("
            UPDATE tb_users SET
                deslogin = :deslogin,
                inadmin  = :inadmin
            WHERE iduser = :iduser
        ", [
            ':deslogin' => $this->getdeslogin(),
            ':inadmin'  => (int)$this->getinadmin(),
            ':iduser'   => $this->getiduser()
        ]);
    }


    /* ==========================
         EXCLUIR USUÁRIO
    ========================== */
public function delete(): void
{
    $sql = new Sql();

    // 1️⃣ Pega o idperson antes de deletar o usuário
    $result = $sql->select(
        "SELECT idperson FROM tb_users WHERE iduser = :id",
        [':id' => $this->getiduser()]
    );

    if (!$result) {
        return;
    }

    $idperson = (int)$result[0]['idperson'];


    // Deleta registros de recuperação de senha primeiro
    $sql->execute(
        "DELETE FROM tb_userspasswordsrecoveries WHERE iduser = :id",
        [':id' => $this->getiduser()]
    );

    // Apaga o registro na tabela de usuários
    $sql->execute(
        "DELETE FROM tb_users WHERE iduser = :id",
        [':id' => $this->getiduser()]
    );

    // Apaga o registro na tabela de pessoas
    $sql->execute(
        "DELETE FROM tb_persons WHERE idperson = :idperson",
        [':idperson' => $idperson]
    );
}
}