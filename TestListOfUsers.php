<?php
    /**
     * PHP code to test listOfUsers work
     */
    require_once 'User.php';
    require_once 'listOfUsers.php';

    $allMale = new listOfUsers('gender', '!=', '0');
    $allMaleArr = $allMale->getAllUsers();
    $allMale->deleteUsers();
