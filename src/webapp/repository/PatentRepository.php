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
        $patent = new Patent($row['user'], $row['title'], $row['description'], $row['date'], $row['file']);
        $patent->setPatentId($row['id']);
        $patent->setCompany($row['company']);
        return $patent;
    }

    public function find($id)
    {
        $statement = $this->pdo->prepare('SELECT * FROM v_patents WHERE id = ?');
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
        $statement = $this->pdo->prepare('SELECT * FROM v_patents');
        $statement->execute();
        $patents = array();
        while ($row = $statement->fetch()) {
            $patents[] = $this->makePatentFromRow($row);
        }
        return new PatentCollection($patents);
    }

    public function search($term)
    {
        $statement = $this->pdo->prepare('SELECT * FROM v_patents WHERE title LIKE :term OR company LIKE :term OR description LIKE :term');
        $statement->execute(array(':term' => '%'.$term.'%'));
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

        if ($result and $statement->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function save(Patent $patent)
    {
        $statement = $this->pdo->prepare('INSERT INTO patents (title, user, file, description, date) VALUES (?, ?, ?, ?, ?)');
        $result = $statement->execute(array(
            $patent->getTitle(),
            $patent->getUser(),
            $patent->getFile(),
            $patent->getDescription(),
            $patent->getDate()
        ));
        return $this->pdo->lastInsertId();
    }
}
