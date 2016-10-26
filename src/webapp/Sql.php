<?php

namespace tdt4237\webapp;

use tdt4237\webapp\models\User;

class Sql
{
    static $pdo;

    function __construct()
    {
    }

    /**
     * Create tables.
     */
    static function up()
    {
        $q[] = "CREATE TABLE users (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, username VARCHAR(50) NOT NULL UNIQUE, password VARCHAR(60), email VARCHAR(50), first_name VARCHAR(50), last_name VARCHAR(50), phone VARCHAR(8), company VARCHAR(50), admin INTEGER NOT NULL DEFAULT 0)";
        $q[] = "CREATE TABLE files (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, name VARCHAR(200), type VARCHAR(200), hash CHAR(32), time INTEGER, user INTEGER NOT NULL, FOREIGN KEY(user) REFERENCES users(id))";
        $q[] = "CREATE TABLE patents (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, user INTEGER NOT NULL, title VARCHAR(100), file INTEGER NULL, description TEXT, date CHAR(8), FOREIGN KEY(user) REFERENCES users(id), FOREIGN KEY(file) REFERENCES files(id))";
        $q[] = "CREATE VIEW v_patents AS SELECT patents.id, patents.user, users.company, patents.title, '/files/' || files.id || '/' || files.name AS file, patents.description, patents.date FROM patents LEFT JOIN users ON patents.user = users.id LEFT JOIN files on patents.file = files.id";

        foreach ($q as $query) {
            self::$pdo->exec($query);
        }

        print "[tdt4237] Done creating all SQL tables.".PHP_EOL;

        self::insertDummyUsers();
        self::insertPatents();
    }

    static function insertDummyUsers()
    {
        $hash1 = Hash::make(bin2hex(openssl_random_pseudo_bytes(12)));
        $hash2 = Hash::make(bin2hex(openssl_random_pseudo_bytes(12)));
        $hash3 = Hash::make(bin2hex(openssl_random_pseudo_bytes(12)));

        $q1 = "INSERT INTO users(username, password, admin, first_name, last_name, phone, company, email) VALUES ('systemmanager', '$hash1', 1, 'Approv', 'Patents', '53290672', 'Patentsy AS', 'systemmanager@patentsy.com')";
        $q2 = "INSERT INTO users(username, password, admin, first_name, last_name, phone, company, email) VALUES ('ittechnican', '$hash2', 1, 'Robert', 'Green', '92300847', 'Patentsy AS', 'ittechnican@patentsy.com')";
        $q3 = "INSERT INTO users(username, password, admin, first_name, last_name, phone, company, email) VALUES ('ceobjarnitorgmund', '$hash3', 1, 'Bjarni', 'Torgmund', '32187625', 'Patentsy AS', 'ceobjarnitorgmund@patentsy.com')";

        self::$pdo->exec($q1);
        self::$pdo->exec($q2);
        self::$pdo->exec($q3);


        print "[tdt4237] Done inserting dummy users.".PHP_EOL;
    }

    static function insertPatents() {
        $q4 = "INSERT INTO patents(user, title, file, description, date) VALUES (1, 'Search System', null, 'New algorithm making search as fast as speed of light.', '20062016')";
        $q5 = "INSERT INTO patents(user, title, file, description, date) VALUES (1, 'New litteum battery technology', null, 'A new technology that will take batteries through a new revolution.', '26072016')";

        self::$pdo->exec($q4);
        self::$pdo->exec($q5);
        print "[tdt4237] Done inserting patents.".PHP_EOL;

    }

    static function down()
    {
        $q[] = "DROP VIEW v_patents";
        $q[] = "DROP TABLE patents";
        $q[] = "DROP TABLE files";
        $q[] = "DROP TABLE users";

        foreach ($q as $query) {
            self::$pdo->exec($query);
        }

        print "[tdt4237] Done deleting all SQL tables.".PHP_EOL;
    }
}
try {
    // Create (connect to) SQLite database in file
    Sql::$pdo = new \PDO('sqlite:app.db');
    // Set errormode to exceptions
    Sql::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    echo $e->getMessage();
    exit();
}
