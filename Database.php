<?php

    class Database
    {
        private $hostname = 'localhost';
        private $username = 'root';
        private $password = '';
        private $database = 'app_test';
        public $conn;

        public function connect()
        {
            $this->conn = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
            if ($this->conn->connect_error) {
                die('Error connection to the database. ' . $this->conn->connect_error);
            }
            $this->createUserTable();
            return $this->conn;
        }

        public function createUserTable()
        {
            $createUserTable = "CREATE TABLE IF NOT EXISTS users
                        (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        firstName varchar(255) NOT NULL,
                        lastName varchar(255) NOT NULL,
                        birthDate datetime NULL,
                        gender bool DEFAULT 0,
                        country varchar(255) NULL
                        )";

            if (!mysqli_query($this->conn, $createUserTable)) {
                die('Error creating table `users`: ' . $this->conn->error);
            }
        }
    }
