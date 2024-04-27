<?php
ini_set('display_errors', 1);
include_once '../db.php';
$db = new dokodb();
$methode = $_SERVER['REQUEST_METHOD'];
if (isset($_SERVER['PATH_INFO'])) {
  $req = explode('/', trim($_SERVER['PATH_INFO'], '/'));
  $playerId = $req[0];
} else {
  $playerId = NULL;
}
$data = file_get_contents('php://input');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 86400');
switch ($methode) {
  case 'GET':
    header('Content-Type: application/json; charset=utf-8');
    if ($playerId == NULL) { // Verzeichnis auflisten
      print(json_encode($db->getAllPlayers()));
    } else
      if ($playerId == 'activeOnly') print(json_encode($db->getAllPlayers(true)));
    elseif (substr($playerId, 0, 9) == "swapSorts") {
      $players = explode(";", $playerId);
      if (isset($players[2]) && $db->swapSort(intval($players[1]), intval($players[2])))  http_response_code(200);
      else http_response_code(400);
    } else print(json_encode($db->getPlayer($playerId)));
    break;
  case 'POST':
    if ($player = json_decode($data, true)) {
      print(json_encode($db->addPlayer($player)));
    }
    break;
  case 'PUT':
    if ($player = json_decode($data, true)) {
      if (isset($player['id'])) {
        print(json_encode($db->updatePlayer($player['id'], $player)));
      }
    }
    break;
  case 'DELETE':
    if ($db->deletePlayer($playerId)) {
      http_response_code(200);
    } else {
      http_response_code(400);
    }
    break;
}
