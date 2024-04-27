<?php
//phpinfo();
include_once '../db.php';
$db = new dokodb();
$methode = $_SERVER['REQUEST_METHOD'];
$data = file_get_contents('php://input');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 86400');
switch ($methode) {
  case 'GET':
    header('Content-Type: application/json; charset=utf-8');
    print(json_encode($db->getStatistics()));
    break;
}
