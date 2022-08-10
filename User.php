<?php
    require_once 'Database.php';

    /**
     * Class User
     * Implements functions to work with User entity:
     * - create/delete new user,
     * - get user's info from DB,
     * - format user to get new StdClass with formatted age and gender
     */
    class User
    {
        public $firstName;
        public $lastName;
        public $birthDate;
        public $gender;
        public $country;
        private $id;

        public $database;
        public $conn;

        const GENDER = [
            '0' => 'male',
            '1' => 'female'
        ];

        /**
         * @param $idOrArray
         * If `id` of the user is used then function gets all User data from DB.
         * In case array is passed for new user creation - new DB entry is created.
         * Validation step is enable for passed array with user's data
         */
        public function __construct($idOrArray)
        {
            $this->database = new Database();
            $this->conn = $this->database->connect();

            if (is_int($idOrArray)) {
                // Get user data from DB
                $user = $this->getUserById($idOrArray);
                if ($user) {
                    $this->id = $idOrArray;
                    $this->firstName = $user['firstName'];
                    $this->lastName = $user['lastName'];
                    $this->birthDate = strtok($user['birthDate'], ' ');
                    $this->gender = $user['gender'];
                    $this->country = $user['country'];
                }
            } else if (is_array($idOrArray)) {
                // Validation
                if (is_string($idOrArray['firstName']) && preg_match('/^[a-zA-Z]+$/', $idOrArray['firstName'])) {
                    $this->firstName = $idOrArray['firstName'];
                } else {
                    die('Error. First name should contain only letters.');
                }

                if (is_string($idOrArray['lastName']) && preg_match('/^[a-zA-Z]+$/', $idOrArray['lastName'])) {
                    $this->lastName = $idOrArray['lastName'];
                } else {
                    die('Error. Last name should contain only letters.');
                }

                if (is_string($idOrArray['birthDate'])) {
                    $date = DateTime::createFromFormat('Y-m-d', $idOrArray['birthDate']);
                    if ($date && $date->format('Y-m-d') === $idOrArray['birthDate']) {
                        $this->birthDate = $idOrArray['birthDate'];
                    } else {
                        die('Error. Birth date is invalid. Format should be Y-m-d (1987-06-15).');
                    }
                }

                if ($idOrArray['gender'] === 0 | $idOrArray['gender'] === 1) {
                    $this->gender = $idOrArray['gender'];
                } else {
                    die('Error. Gender should be decoded as 0/1.');
                }

                if (is_string($idOrArray['country'])) {
                    $this->country = $idOrArray['country'];
                } else {
                    die('Error. Country is not defined.');
                }

                // Save to DB
                $id = $this->save();
                $this->id = $id;
            }
        }

        /**
         * @param int $id
         * @return mixed|void
         */
        protected function getUserById(int $id)
        {
            $query = $this->conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $query->bind_param('i', $id);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows > 0) {
                $userData = [];
                while ($row = $result->fetch_assoc()) {
                    $userData[] = $row;
                }
                return $userData[0];
            } else {
                die('User with id=' . $id . ' does not exists');
            }
        }

        /**
         * @return int|string|void
         */
        protected function save()
        {
            $query = $this->conn->prepare("INSERT INTO `users` (firstName, lastName, birthDate, gender, country) "
                                                . "VALUES (?, ?, ?, ?, ?)");
            $query->bind_param('sssis', $this->firstName, $this->lastName, $this->birthDate, $this->gender, $this->country);
            $query->execute();

            if ($this->conn->affected_rows > 0) {
                return $this->conn->insert_id;
            } else {
                die('Error saving to DB.');
            }
        }


        /**
         * @return bool|void
         */
        public function deleteUser()
        {
            $query = $this->conn->prepare("DELETE FROM `users` WHERE id = ?");
            $query->bind_param('i', $this->id);
            $query->execute();

            if ($this->conn->affected_rows === 0) {
                die('Error deleting user');
            }
            else {
                return true;
            }
        }

        /**
         * @return StdClass
         */
        public function formatUser()
        {
            $newUser = new StdClass();
            $newUser->firstName = $this->firstName;
            $newUser->lastName = $this->lastName;
            $newUser->birthDate = self::birthDateToAge($this->birthDate);
            $newUser->gender = self::genderToString($this->gender);
            $newUser->country = $this->country;
            return $newUser;
        }

        /**
         * @param string $birthDate
         * @return int
         */
        public static function birthDateToAge(string $birthDate)
        {
            $birthDateUpd = DateTime::createFromFormat('Y-m-d', $birthDate);
            $now = new DateTime();
            $yearDiff = $now->format('Y') - $birthDateUpd->format('Y');
            if ($now > $birthDateUpd->modify('+' . $yearDiff . ' years')) {
                return $yearDiff;
            } else {
                return $yearDiff - 1;
            }
        }

        /**
         * @param bool $gender
         * @return string
         */
        public static function genderToString(bool $gender)
        {
            return self::GENDER[$gender];
        }
    }

