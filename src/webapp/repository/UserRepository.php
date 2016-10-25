<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\Phone;
use tdt4237\webapp\models\Email;
use tdt4237\webapp\models\NullUser;
use tdt4237\webapp\models\User;

class UserRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function makeUserFromRow(array $row)
    {
        $user = new User($row['username'], $row['password'], $row['first_name'], $row['last_name'], $row['phone'], $row['company']);
        $user->setUserId($row['id']);
        $user->setFirstName($row['first_name']);
        $user->setLastName($row['last_name']);
        $user->setPhone($row['phone']);
        $user->setCompany($row['company']);
        $user->setIsAdmin($row['admin']);

        if (!empty($row['email'])) {
            $user->setEmail(new Email($row['email']));
        }

        if (!empty($row['phone'])) {
            $user->setPhone(new Phone($row['phone']));
        }

        return $user;
    }

    public function getNameByUsername($username)
    {
        $statement = $this->pdo->prepare('SELECT given_name, last_name FROM users WHERE username = ?');
        $statement->execute(array($username));
        $result = $statement->fetch();
        if ($result !== false) {
            return ($result['first_name'].' '.$result['last_name']);
        } else {
            return false;
        }
    }

    public function findByUser($username)
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $statement->execute(array($username));
        $result = $statement->fetch();
        if ($result !== false) {
            return $this->makeUserFromRow($result);
        } else {
            return false;
        }
    }

    public function deleteByUsername($username)
    {
        $statement = $this->pdo->prepare('DELETE FROM users WHERE username = ?');
        $result = $statement->execute(array($username));

        if ($result and $statement->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function all()
    {
        $statement = $this->pdo->prepare('SELECT * FROM users');
        $statement->execute();
        $users = array();
        while ($row = $statement->fetch()) {
            $users[] = $this->makeUserFromRow($row);
        }
        return $users;
    }

    public function save(User $user)
    {
        if ($user->getUserId() === null) {
            $this->createUser($user);
        } else {
            $this->updateUser($user);
        }
    }

    private function createUser(User $user)
    {
        $statement = $this->pdo->prepare('INSERT INTO users (username, password, first_name, last_name, company, phone, admin) VALUES(?, ?, ?, ?, ?, ?, ?)');
        $result = $statement->execute(array(
            $user->getUsername(),
            $user->getHash(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getCompany(),
            $user->getPhone(),
            $user->isAdmin()
        ));

        if ($result and $statement->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function updateUser(User $user)
    {
        $statement = $this->pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, company = ?, phone = ?, email = ?, admin = ? WHERE id = ?');
        $result = $statement->execute(array(
            $user->getFirstName(),
            $user->getLastName(),
            $user->getCompany(),
            $user->getPhone(),
            $user->getEmail(),
            $user->isAdmin(),
            $user->getUserId()
        ));

        if ($result and $statement->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
