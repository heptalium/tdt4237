<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\File;

class FileRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function makeFileFromRow(array $row)
    {
        $file = new File($row['id'], $row['name'], $row['type'], $row['hash'], $row['time']);
        return $file;
    }

    public function createFile($file) {
        $statement = $this->pdo->prepare('INSERT INTO files (name, type, hash, time) VALUES (?, ?, ?, ?)');
        $result = $statement->execute(array(
            $file->getName(),
            $file->getType(),
            $file->getHash(),
            $file->getTime(),
        ));
        return $this->pdo->lastInsertId();
    }

    public function getById($id)
    {
        $statement = $this->pdo->prepare("SELECT * FROM files WHERE id = ?");
        $statement->execute(array($id));
        $result = $statement->fetch();
        if ($result !== false) {
            return $this->makeFileFromRow($result);
        } else {
            return false;
        }
    }

    public function getByName($name)
    {
        $statement = $this->pdo->prepare("SELECT * FROM files WHERE name = ?");
        $statement->execute(array($name));
        $result = $statement->fetch();
        if ($result !== false) {
            return $this->makeFileFromRow($result);
        } else {
            return false;
        }
    }

    public function deleteById($patentId)
    {
        $statement = $this->pdo->prepare("DELETE FROM files WHERE id = ?");
        return $statement->execute(array($id));
    }
}
