<?php

echo $_SERVER['REQUEST_METHOD'];
var_dump($_GET);
var_dump($_POST);
echo file_get_contents('php://input');
