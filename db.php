<?php
require_once (__DIR__ . "/.dbcredentials.php.inc");
class dokodb
{
  private $listId;
  private $date;
  private $db;
  public function __construct()
  {

    $this->db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($this->db->connect_errno) {
      die("Failed to connect to MySQL: (" . $this->db->connect_errno . ") " . $this->db->connect_error);
    }
    $this->setListId();
    $this->date = date("Y-m-d");
  }
  function setListId()
  {
    $res = $this->db->query("SELECT activeListId FROM counter")->fetch_array();
    if (!$res[0])
      $this->listId = 1;
    else
      $this->listId = $res[0];
    return;
  }
  function getNewListId()
  {
    $gameCount = $this->db->query("SELECT count(gameId) FROM games WHERE listId='" . $this->listId . "'")->fetch_array()[0];
    if ($gameCount > 0) {
      $this->listId++;
      $this->db->query("UPDATE counter set activeListId=" . $this->listId);
    }
  }
  function getAllPlayers($onlyActive = false)
  {
    $i = 0;
    $condition = "";
    if ($onlyActive) {
      $condition = "WHERE `active`=1";
    }
    $res = $this->db->query("SELECT `id`,`name`,`surname`,`active`,`sort` FROM players $condition ORDER BY `sort`");
    while ($player = $res->fetch_assoc()) {
      $player['active'] = $player['active'] == 0 ? false : true; //mysql only returns 0 or 1 for boolean values
      $players[$i++] = $player;
    }
    return $players;
  }
  function getPlayer($id)
  {
    $playerid = mysqli_real_escape_string($this->db, $id);
    $query = "SELECT * FROM players WHERE `id`=$playerid";
    $res = $this->db->query($query);
    $player = $res->fetch_assoc();
    if (isset($player['active']))
      $player['active'] = $player['active'] == 0 ? false : true; //mysql only returns 0 or 1 for boolean values
    else
      $player = NULL;
    return $player;
  }
  function swapSort($player1, $player2)
  {
    if (is_int($player1) && is_int($player2)) {
      $query = "SELECT `sort` FROM players WHERE `id`=$player1";
      $res = $this->db->query($query);
      if ($res->num_rows == 1) {
        $sort1 = $res->fetch_array()[0];
        $query = "SELECT `sort` FROM players WHERE `id`=$player2";
        $res = $this->db->query($query);
        if ($res->num_rows == 1) {
          $sort2 = $res->fetch_array()[0];
          $query = "UPDATE players set `sort`=$sort2 WHERE `id`=$player1";
          $this->db->query($query);
          $query = "UPDATE players set `sort`=$sort1 WHERE `id`=$player2";
          $this->db->query($query);
          return true;
        }
      }
    }
    return false;
  }
  function updatePlayer($id, $player)
  {
    foreach ($player as $key => $value) {
      $key = mysqli_real_escape_string($this->db, $key);
      $value = mysqli_real_escape_string($this->db, $value);
      $id = intval($id);
      if ($key === "active") {
        $value = $value == false ? 0 : 1;
      }
      $sql = "UPDATE players SET `$key`='$value' WHERE `id`=$id";
      $result = $this->db->query($sql);
      if ($result === FALSE) {
        die($this->db->error);
      }
    }
    return $this->getPlayer($id);
  }
  function addPlayer($player)
  {
    $playerFields = $this->_getTableInfo('players');
    $player['id'] = $this->getNextPlayerId();
    $player['sort'] = $player['id'];
    $query = "INSERT INTO players SET ";
    foreach ($playerFields as $fieldname => $fieldinfo) {
      $query .= "`$fieldname`=";
      $value = mysqli_real_escape_string($this->db, $player[$fieldname]);
      if ($playerFields[$fieldname]['Type'] == "tinyint(1)") { //convert boolean to 0/1
        $value = $value == false ? 0 : 1;
      }
      if (isset($player[$fieldname])) {
        $query .= "'$value',";
      } else {
        $query .= "'',";
      }
    }
    $query = substr($query, 0, -1);
    $this->db->query($query);
    return $this->getPlayer($player['id']);
  }
  function deletePlayer($id)
  {
    $id = intval($id);
    if ($id > 0) {
      if ($res = $this->db->query("DELETE FROM players WHERE `id`=$id")) {
        return true;
      }
    }
    return false;
  }
  function getNextPlayerId()
  {
    $maxId = $this->db->query("SELECT MAX(`id`) FROM players")->fetch_array();
    return $maxId[0] + 1;
  }
  private function _getTableInfo($table)
  {
    $res = $this->db->query("DESCRIBE `$table`");
    $fields = [];
    while ($colInfo = $res->fetch_assoc()) {
      $fields[$colInfo['Field']] = $colInfo;
    }
    return $fields;
  }

  function getActualResults()
  {
    $activeListId = $this->db->query("SELECT activeListId FROM counter")->fetch_array()[0];
    $games = [];
    if ($activeListId != NULL) {
      $res = $this->db->query("SELECT playerId,gameId,points,kontrare FROM games
                              WHERE listId='$activeListId' ORDER BY gameId");
      if ($res->num_rows == 0) {
        $games[0]['players'] = [];
        $players = $this->getAllPlayers(true);
        foreach ($players as $no => $player) {
          array_push($games[0]['players'], ['playerId' => $player['id'], 'points' => 0, 'party' => 1]);
        }
      } else {
        while ($game = $res->fetch_assoc()) {
          $k = $game["kontrare"] == 'Re' ? 0 : 1;
          if (!isset($games[$game["gameId"]]))
            $games[$game["gameId"]]['players'] = [];
          array_push($games[$game["gameId"]]['players'], ['playerId' => $game["playerId"], 'points' => $game["points"], 'party' => $k]);
        }
      }
    }
    return $games;
  }
  function addGame($results, $changeLastGame)
  {
    $gameId = $this->getLastGameId();
    if (!$changeLastGame) {
      $gameId++;
    }
    foreach ($results as $player => $result) {
      $fields = explode(";", $result);
      $points = $fields[0];
      $kontrare = $fields[1];
      $angesagt = $fields[2];
      $gespielt = $fields[3];
      $addpoints = $fields[4];
      $solo = $fields[5];
      $sqlcmd = "INSERT";
      if ($changeLastGame)
        $sqlcmd = "REPLACE";
      $query = "$sqlcmd INTO games (playerId,date,listId,gameId,points,kontrare,extrapoints,solo,pronounced,played) VALUES
      ($player,'$this->date',$this->listId,$gameId,$points,'$kontrare',$addpoints,$solo,$angesagt,$gespielt)";
      $this->db->query($query);
    }
  }
  function getLastGameId()
  {
    $res = $this->db->query("SELECT gameId FROM games WHERE listId='$this->listId' ORDER BY gameId DESC LIMIT 1")->fetch_array();
    if (!isset($res[0]))
      $res[0] = 0;
    return $res[0];
  }
  function getStatistics()
  {
    $statistics = [];
    $players = $this->getAllPlayers();
    foreach ($players as $player) {
      $playerid = $player["id"];
      $statistics[$playerid] = [];
      $statistics[$playerid]["Gespielt"] = $this->db->query("SELECT COUNT(*) FROM games WHERE playerId=$playerid")->fetch_array()[0];
      $reSpiele = $this->db->query("SELECT COUNT(*) FROM games WHERE playerId=$playerid AND kontrare='Re'")->fetch_array()[0];
      $kontraSpiele = $this->db->query("SELECT COUNT(*) FROM games WHERE playerId=$playerid AND kontrare='Kontra'")->fetch_array()[0];
      $gesamtSpiele = $reSpiele + $kontraSpiele;
      $gewonneneSpiele = $this->db->query("SELECT COUNT(*) FROM games WHERE playerId=$playerid AND points>0")->fetch_array()[0];
      if ($gesamtSpiele > 0) {
        $reProzent = round($reSpiele / $gesamtSpiele * 100);
        $gewonneneProzent = round($gewonneneSpiele / $gesamtSpiele * 100);
      } else {
        $reProzent = 0;
        $gewonneneProzent = 0;
      }
      $statistics[$playerid]["ReProzent"] = $reProzent;
      $statistics[$playerid]["Gewonnen"] = $gewonneneProzent;
      $statistics[$playerid]["Soli"] = $this->db->query("SELECT COUNT(*) FROM games WHERE playerId=$playerid AND solo=1 AND kontrare='Re'")->fetch_array()[0];
      $statistics[$playerid]["Soli gewonnen"] = $this->db->query("SELECT COUNT(*) FROM games WHERE playerId=$playerid AND solo=1 AND kontrare='Re' AND points>0")->fetch_array()[0];
      $statistics[$playerid]["Gesamtpunkte"] = $this->db->query("SELECT SUM(points) FROM games WHERE playerId=$playerid")->fetch_array()[0];
      if ($statistics[$playerid]["Gespielt"] > 0) {
        $statistics[$playerid]["Punkteschnitt"] = number_format($statistics[$playerid]["Gesamtpunkte"] / $statistics[$playerid]["Gespielt"], 1);
      } else {
        $statistics[$playerid]["Punkteschnitt"] = "0.0";
      }
    }
    return $statistics;
  }

  function saveGame($gameData)
  {
    //result=-4:Re schwarz,-3=Re3 -2=Re6 -1=Re9 0=re Gewonnen, 1=Kontra gewonnen, 5=Kontra schwarz gewonnen
    //extrapoints -5 bis -1 =Re-Extrapunkte, 1 bis 5 = Kontra-Extrapunkte
    //kontraAnsage 0=keine Ansage 1=Kontra, keine 9 bis 5 = schwarz
    //reAnsage 0=keine Ansage -1=Re,-2=keine 9, -5=schwarz
    //player['party']==0 : Re, player['party']==1 : Kontra
    $result = $gameData['result'];
    $extrapoints = $gameData['extrapoints'];
    $kontraAnsage = $gameData['kontraAnsage'];
    $reAnsage = $gameData['reAnsage'];
    $changeLastGame = $gameData['changeLastGame'];
    $points = $extrapoints;
    $multi = 1;
    if ($reAnsage < 0) {
      $multi = 2;
      $reAnsage++;
    }
    if ($kontraAnsage > 0) {
      $multi *= 2;
      $kontraAnsage--;
    }
    $level = $result < 1 ? $result - 1 : $result;
    //$level ist jetzt -1 bis -5 bei Re-Gewinn, oder +1 bis +5 bei Kontra-Gewinn
    if ($level < 0) { //Re has more Points
      if ($reAnsage <= $level) { //Re has not reached Goal
        if ($kontraAnsage > 0) { //Kontra has said u9 - sw
          $level = $reAnsage - $level - $kontraAnsage + 1;
        } else {
          $level = -($reAnsage - 1 - $level) + 1;
        }
      } else {
        $level += $reAnsage - $kontraAnsage;
      }
    } else { //Kontra has more Points
      if ($kontraAnsage >= $level) { //Kontra has not reached Goal
        if ($reAnsage < 0) { //Re has also not reached goal
          $level = $level - $kontraAnsage - $reAnsage + 1;
        } else {
          $level = -($kontraAnsage + 1 - $level);
        }
      } else {
        $level += $kontraAnsage + 1 - $reAnsage;
      }
    }
    $points += $level;
    $points *= $multi * 5;
    $reCount = 0;
    foreach ($gameData['playerStates'] as $playerId => $player) {
      if ($player['party'] == 0)
        $reCount++;
    }
    $solo = $reCount == 1 ? 1 : 0;
    $results = [];

    foreach ($gameData['playerStates'] as $player) {
      $party = $player['party'] == 0 ? "Re" : "Kontra";
      $playerpoints = $points;
      $ansage = $party == "Re" ? $reAnsage : $kontraAnsage;
      $soloPlayer = 0;
      if ($party == "Re" && $solo == 1) {
        $playerpoints *= 3;
        $soloPlayer = 1;
      }
      $playerId = $player['playerId'];
      $playerpoints = $party == "Re" ? -$playerpoints : $playerpoints;
      $results[$playerId] = "$playerpoints;$party;$ansage;$result;$extrapoints;$soloPlayer";
    }
    //print(json_encode($results));
    $this->addGame($results, $changeLastGame);
  }
  function startNewList()
  {
    $this->getNewListId();
  }
  function createTables()
  {
    $this->db->query("CREATE TABLE `counter` (`activeListId` int(11) NOT NULL, PRIMARY KEY (`activeListId`))");
    $this->db->query("INSERT INTO `counter` SET `activeListId`=10000");
    print ("Erzeuge Games-Tabelle<br />");
    $ok = $this->db->query("CREATE TABLE `games` (
      `listId` int(11) NOT NULL,
      `gameId` int(11) NOT NULL,
      `playerId` int(11) NOT NULL,
      `date` date NOT NULL,
      `points` int(11) NOT NULL,
      `kontrare` char(6) NOT NULL,
      `extrapoints` int(11) NOT NULL,
      `solo` int(11) NOT NULL,
      `pronounced` int(11) NOT NULL,
      `played` int(11) NOT NULL,
      PRIMARY KEY (`listId`,`gameId`,`playerId`))");
    if ($ok == false) {
      print ($this->db->error);
    }
    print ("Erzeuge players-Tabelle <br />");
    $ok = $this->db->query("CREATE TABLE `players` (
      `id` int(11) NOT NULL,
      `name` varchar(30) NOT NULL,
      `surname` varchar(30) NOT NULL,
      `active` tinyint(1) NOT NULL,
      PRIMARY KEY (`id`))");
    if ($ok == false) {
      print ($this->db->error);
    }
    $spieler = array(
      array('spielerid' => '1', 'vorname' => 'Jens', 'name' => 'Wulf'),
      array('spielerid' => '2', 'vorname' => 'Alex', 'name' => 'Wulf'),
      array('spielerid' => '3', 'vorname' => 'Britta', 'name' => 'Krusche'),
      array('spielerid' => '4', 'vorname' => 'Michael', 'name' => 'Krusche'),
      array('spielerid' => '5', 'vorname' => 'Bernd', 'name' => 'Ploch'),
      array('spielerid' => '6', 'vorname' => 'Udo', 'name' => 'Weickert'),
      array('spielerid' => '7', 'vorname' => 'Klaus', 'name' => 'Stephan')
    );
    foreach ($spieler as $s) {
      $sql = "INSERT INTO players VALUES (?,?,?,0)";
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('iss', $s['spielerid'], $s['vorname'], $s['name']);
      $stmt->execute();
    }
    require_once ("spielliste.php");
    //  array('spielerid' => '1','datum' => '2020-10-04','listenid' => '2','spielid' => '1','punkte' => '0','kontrare' => 'Re','zusatzpunkte' => '0','solo' => '0','angesagt' => '0','gespielt' => '-2'),
    foreach ($games as $g) {
      $sql = "INSERT INTO games VALUES(?,?,?,?,?,?,?,?,?,?)";
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('iiisisiiii', $g['listId'], $g['gameId'], $g['playerId'], $g['date'], $g['points'], $g['kontrare'], $g['extrapoints'], $g['solo'], $g['pronounced'], $g['played']);
      /*array(
        'listId' => $listId,
        'gameId' => $spieler['spielid'],
        'playerId' => $spieler['spielerid'],
        'date' => $spieler['datum'],
        'points' => $points[$spieler['kontrare']],
        'kontrare' => $spieler['kontrare'],
        'extrapoints' => $zusatzpunkte,
        'solo' => $solo,
        'pronounced' => -$spieler['angesagt'],
        'played' => -$spieler['gespielt']
      )*/

      $stmt->execute();
    }
  }
}
//$db = new dokodb();
//$db->createTables();
// //result=-4:Re schwarz,-3=Re3 -2=Re6 -1=Re9 0=re Gewonnen, 1=Kontra gewonnen, 5=Kontra schwarz gewonnen
// //extrapoints -5 bis -1 =Re-Extrapunkte, 1 bis 5 = Kontra-Extrapunkte
// //kontraAnsage 0=keine Ansage 1=Kontra, keine 9 bis 5 = schwarz
// //reAnsage 0=keine Ansage -1=Re,-2=keine 9, -5=schwarz
// //player['party']==0 : Re, player['party']==1 : Kontra
// $gameData['playerStates'] = array(
//   1 => array('name' => 'Michael', 'party' => 1),
//   2 => array('name' => 'Bernd', 'score' => 215, 'party' => 0),
//   3 => array('name' => 'Klaus', 'score' => 130, 'party' => 1),
//   4 => array('name' => 'Udo', 'score' => 215, 'party' => 1)
// );
// $gameData['result'] = 0;
// $gameData['extrapoints'] = 0;
// $gameData['kontraAnsage'] = 0;
// $gameData['reAnsage'] = 0;
// $gameData['changeLastGame'] = 0;
// dokodb::saveGame($gameData);
