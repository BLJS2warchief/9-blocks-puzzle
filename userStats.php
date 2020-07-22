<?php
  $gClientId_8000 = "xoxoxox";
  $gClientId = "xoxoxox";
  $userEmail = "";

  if ( isset($_REQUEST['debug']) ) error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
  else error_reporting(0);

  switch ($_REQUEST['req']) {
    case "store":
      storeGameData();
      break;
    case "select":
      selectGameData();
      break;
    default:
      echo "Request (req=" . $_REQUEST['req'] . ") not supported";
  }
return;
  php_uname();
//
function storeGameData() {
  global $userEmail;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $sql3 = "INSERT INTO dbo.userStats(email,steps,elapsedTime,dateTimeStamp) VALUES (:email,:steps,:elapsed,:dateTime)";}
else {
  $sql3 = "INSERT INTO userStats(email,steps,elapsedTime,dateTimeStamp) VALUES (:email,:steps,:elapsed,:dateTime)";

}

  if (( isset($_REQUEST['id']) and $_REQUEST['id'] != '' ) and
      ( isset($_REQUEST['steps']) and $_REQUEST['steps'] != '' and is_numeric($_REQUEST['steps'])) and
      ( isset($_REQUEST['elapsed']) and $_REQUEST['elapsed'] != '' )) {
    if (!validGoogleUser())
      $userEmail = "guest";
//    try{
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $myPDO = new PDO('sqlsrv:server=localhost;Database=gameStats', 'gameStats', 'gameStats');
  }
  else {
    $myPDO = new PDO('mysql:host=127.0.0.1;port=3306;dbname=gameStats', 'gameStats', 'gameStats');
     
  }    
      $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $statement = $myPDO->prepare($sql3);
      $dateTime = date("Y-m-d H:i:s");
      $result = $statement->execute(array( "email" => $userEmail, "steps" => $_REQUEST['steps'], "elapsed" => $_REQUEST['elapsed'], "dateTime" =>  $dateTime));
      $sqlState = $statement->errorCode();

      if ($sqlState != 0)
        echo " ERROR: While game data save (error code = ," . $sqlState . ")";
      else
        echo " , Game data save successful...";
//    } catch (PDOException $e) {
//      echo " ERROR: While game data save (" . $e->getMessage() . ")";
//    }
  } else echo " ERROR: Game data not supplied / invalid ...";
  return;
}

//
function selectGameData() {
  global $userEmail;
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $sql1 = "SELECT TOP 5 * FROM dbo.userStats ";
  }
  else {
    $sql1 = "SELECT * FROM userStats ";
  }
  $sqlWhere = "";
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $sql2 = " ORDER BY dateTimeStamp DESC";
  }
  else {
    $sql2 = " ORDER BY dateTimeStamp DESC LIMIT 0, 5 ";
  }

  if ((  isset($_REQUEST['id']) and $_REQUEST['id'] != '' ) and
      ( !isset($_REQUEST['steps']) or $_REQUEST['steps'] == '' ) and
      ( !isset($_REQUEST['elapsed']) or $_REQUEST['elapsed'] == '' )) {
// validate the id is really logged into Google
    if (validGoogleUser())
      $sqlWhere = " WHERE email = '" . $userEmail . "' ";
  }
  if ( isset($_REQUEST['stop']) ) return;
  if ( isset($_REQUEST['debug']) ) echo $sql1 . $sqlWhere . $sql2 . '<br>';
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $myPDO = new PDO('sqlsrv:server=localhost;Database=gameStats', 'gameStats', 'gameStats');
  }
  else{
    echo "test";
    $myPDO = new PDO('mysql:host=127.0.0.1;port=3306;dbname=gameStats', 'gameStats', 'gameStats');
  }
  $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  try{
  	$result = $myPDO->query($sql1 . $sqlWhere . $sql2);
  }catch(PDOException $e) {
     echo $e->getMessage();
     return;
  }

  $userData = "";
//Format $result into $userData
  $userData = '<I> Previous Scores </I> <TABLE>';
  foreach($result as $row){
    $userData = $userData . '<TR><TD class = "gameId">' . $row['email'] . '</TD>';
    $userData = $userData . '<TD class = "gameSteps">' . $row['steps'] . ' steps' . '</TD>';
    $userData = $userData . '<TD class = "gameElapsed">' . $row['elapsedTime'] . '</TD>';
    $userData = $userData . '</TR>';
  }
  $userData = $userData . '</TABLE>';

  echo $userData; //echo "____5555____";
  return;
}

//Input: none
//Output:true - if a user is logged into google
//       false - if not logged in
//Steps:
//   Retrieve token id information from google
//   If token info and google client id are valid save email
function validGoogleUser() {
  global $userEmail;
  global $gClientId;
  global $gClientId_8000;

  $tokenInfo = file_get_contents('https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $_REQUEST["id"]);

//	var_dump($tokenInfo);
  if ($tokenInfo){
    $info = json_decode($tokenInfo, true);
    //var_dump($info);
    if ( isset($_REQUEST['debug']) ) print_r($tokenInfo);
    $userEmail = $info['email'];
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      if($info['aud'] == $gClientId)
        return true;
    }
    else {
      //var_dump($info);
      if($info['aud'] == $gClientId_8000)
        return true;
    }
  }
return false;
}
?>
