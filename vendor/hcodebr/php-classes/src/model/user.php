<?php

namespace Hcode\Model;

use Exception;
use Hcode\DB\Sql;
use Hcode\Model;

class User extends Model {

    const SESSION = "User";

    public static function login($login, $password)
    {
        $sql = new Sql();

        $results = $sql->select(
            "SELECT * FROM tb_users WHERE deslogin = :LOGIN",
            [
                ":LOGIN" => $login
            ]
        );

        if (count($results) === 0) {
            throw new Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"])) {

            $user = new User();
            $user->setData($data);

            $_SESSION[self::SESSION] = $user->getValues();

            return $user;

        } else {
            throw new Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function checkLogin()
    {
        return (
            !isset($_SESSION[self::SESSION]) &&
            $_SESSION[self::SESSION]["iduser"] > 0
        );
    }

    public static function logout()
    {
        $_SESSION[self::SESSION] = null;
    }

        public static function listall()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }
}
