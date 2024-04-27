<?php
include_once '../db.php';
$db = new dokodb();
$methode = $_SERVER['REQUEST_METHOD'];
$req = NULL;
if (isset($_SERVER['PATH_INFO'])) $req = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$data = file_get_contents('php://input');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET,POST');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 86400');
switch ($methode) {
  case 'GET':
    if (isset($req[0]) && $req[0] == "startgame") {
      $db->startNewList();
    } else {
      header('Content-Type: application/json; charset=utf-8');
      print(json_encode($db->getActualResults()));
    }
    break;
  case 'POST':
    if ($game = json_decode($data, true)) {
      print(json_encode($db->saveGame($game)));
    }
    break;
  case 'PUT':
    break;
  case 'DELETE':
    break;
}
