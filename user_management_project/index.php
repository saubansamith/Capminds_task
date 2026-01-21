<?php

// require_once must load, otherwise program stops
require_once "data/users.php";
require_once "utils/User.php";
require_once "utils/Validator.php";

include_once "utils/helpers.php";

use utils\User;
use utils\Validator as userValidator;

$users = require "data/users.php";
$validator = new userValidator();

foreach ($users as $u){
    $user = new User($u['username'], $u['email'], $u['password']);

    $user-> displayUser();

    echo "username: " . $validator->validateUsername($user->username) . "<br>"; 
    echo "Email: " . $validator->validateEmail($user->email) . "<br>"; 
    echo "Password: " . $validator->validatePassword($user->password) . "<br>"; 

    printLine();
}

?>