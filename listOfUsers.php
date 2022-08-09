<?php

    require_once 'Database.php';

    class listOfUsers
    {
        public $idArray;
        public $conn;

        public function __construct($column, $operator, $value)
        {
            $this->checkUserClass();

            if (in_array($column, ['firstName', 'lastName', 'birthDay', 'gender', 'country'])) {
                if (in_array($operator, ['>', '<', '!='])) {
                    $database = new Database();
                    $this->conn = $database->connect();

                    $query = $this->conn->prepare('SELECT id FROM `users` WHERE '
                        . $column . ' '
                        . $operator
                        . ' '
                        . $value);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $this->idArray[] = $row['id'];
                        }
                        return $this->idArray;
                    } else {
                        die('Result query is empty. Aborting...');
                    }

                } else {
                    die("Supported operators are ['>', '<', '!=']");
                }
            } else {
                die("Supported columns are ['firstName', 'lastName', 'birthDay', 'gender', 'country']");
            }
        }

        public function getAllUsers()
        {
            $allUsers = [];
            foreach ($this->idArray as $id) {
                $allUsers[] = new User($id);
            }
            return $allUsers;
        }

        public function deleteUsers()
        {
            foreach ($this->idArray as $id) {
                $user = new User($id);
                $user->deleteUser();
            }
            return true;
        }

        private function checkUserClass()
        {
            if (!class_exists('User')) {
                die('Class User is not declared.');
            }
        }

    }

