<?php
namespace App\Models;

use App\DB\Sql;


class Category
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

    public function getidcategory(): int
    {
        return (int)($this->data['idcategory'] ?? 0);
    }

    public function getdescategory(): string
    {
        return $this->data['descategory'] ?? '';
    }

    public static function listAll(): array
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY idcategory ASC");
    }

    public function get(int $id): void
    {
        $sql = new Sql();
        $result = $sql->select(
            "SELECT * FROM tb_categories WHERE idcategory = :id",
            [':id' => $id]
        );

        if ($result) {
            $this->setData($result[0]);
        }
    }

    public function save(): void
    {
        $sql = new Sql();

        if ($this->getidcategory() > 0) {
            $sql->execute(
                "UPDATE tb_categories 
                 SET descategory = :descategory 
                 WHERE idcategory = :id",
                [
                    ':descategory' => $this->getdescategory(),
                    ':id'          => $this->getidcategory()
                ]
            );
        } else {
            $sql->execute(
                "INSERT INTO tb_categories (descategory) 
                 VALUES (:descategory)",
                [
                    ':descategory' => $this->getdescategory()
                ]
            );
        }
    }

    public function delete(): void
    {
        $sql = new Sql();
        $sql->execute(
            "DELETE FROM tb_categories WHERE idcategory = :id",
            [':id' => $this->getidcategory()]
        );
    }
}
