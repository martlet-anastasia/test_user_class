<?php
    require_once 'Database.php';

    /**
     * Class listOfUsers
     * Works in pair with class User to work with lists of User objects
     */
    class listOfUsers
    {
        public $idArray;
        public $conn;

        /**
         * @param $column
         * @param $operator
         * @param $value
         * Implements search across all DB columns
         */
        public function __construct($column, $operator, $value)
        {
            $this->checkUserClass();

            if (in_array($column, ['firstName', 'lastName', 'birthDay', 'gender', 'country'])) {
                if (in_array($operator, ['>', '<', '!='])) {
                    $database = new Database();
                    $this->conn = $database->connect();

                    $query = $this->conn->prepare("SELECT id FROM `users` WHERE "
                                                        . $column . " "
                                                        . $operator . " "
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

        /**
         * @return array
         */
        public function getAllUsers()
        {
            $allUsers = [];
            foreach ($this->idArray as $id) {
                $allUsers[] = new User($id);
            }
            return $allUsers;
        }

        /**
         * @return bool
         */
        public function deleteUsers()
        {
            foreach ($this->idArray as $id) {
                $user = new User($id);
                $user->deleteUser();
            }
            return true;
        }

        /**
         * Function to check if class User exists
         */
        private function checkUserClass()
        {
            if (!class_exists('User')) {
                die('Class User is not declared.');
            }
        }
    }

