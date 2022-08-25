<?php

$root = dirname(__FILE__);
$root = str_replace('\\', '/', $root);
define('ROOT', $root);
require_once ROOT . '/Core/autoload.php';
require_once ROOT . '/Core/helpers.php';

use Core\Database\DB;

class User
{
}

$db = new DB;
$new_id = $db->insertGetId("insert into users(name) values('Alex')");
echo $new_id;