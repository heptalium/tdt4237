<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\Patent;
use tdt4237\webapp\models\PatentCollection;

class PatentRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function makePatentFromRow(array $row)
    {
        $patent = new Patent($row['id'], $row['company'], $row['title'], $row['description'], $row['date'], $row['file']);
        $patent->setPatentId($row['id']);
        $patent->setCompany($row['company']);
        $patent->setTitle($row['title']);
        $patent->setDescription($row['description']);
        $patent->setDate($row['date']);
        $patent->setFile($row['file']);

        return $patent;
    }

    public function find($id)
    {
        $statement = $this->pdo->prepare('SELECT * FROM patents WHERE id = ?');
        $statement->execute(array($id));
        $result = $statement->fetch();
        if ($result !== false) {
            return $this->makePatentFromRow($result);
        } else {
            return false;
        }
    }

    public function all()
    {
        $statement = $this->pdo->prepare('SELECT * FROM patents');
        $statement->execute();
        $patents = array();
        while ($row = $statement->fetch()) {
            $patents[] = $this->makePatentFromRow($row);
        }
        return new PatentCollection($patents);
    }

    public function deleteByPatentid($id)
    {
        $statement = $this->pdo->prepare('DELETE FROM patents WHERE id = ?');
        $result = $statement->execute(array($id));
        return $result;
    }


    public function save(Patent $patent)
    {
        $statement = $this->pdo->prepare('INSERT INTO patents (title, company, file, description, date) VALUES (?, ?, ?, ?, ?)');
        $result = $statement->execute(array(
            $patent->getTitle(),
            $patent->getCompany(),
            $patent->getFile(),
            $patent->getDescription(),
            $patent->getDate()
        ));
        return $this->pdo->lastInsertId();
    }
}
