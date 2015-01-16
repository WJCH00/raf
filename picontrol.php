<?php
session_start();
//////////////////////////////
// EDIT THESE TWO VARIABLES //
//////////////////////////////
$MySQLUsername = "root";
$MySQLPassword = "qwerty";



/////////////////////////////////
// DO NOT EDIT BELOW THIS LINE //
/////////////////////////////////
$MySQLHost = "localhost";
$MySQLDB = "raspberry_mysql";

If (($MySQLUsername == "USERNAME HERE") || ($MySQLPassword == "PASSWORD HERE")){
	print 'ERROR - Please set up the script first';
	exit();
}

$dbConnection = mysql_connect($MySQLHost, $MySQLUsername, $MySQLPassword);
mysql_select_db($MySQLDB, $dbConnection);
If (isset($_POST['action'])){
	If ($_POST['action'] == "setPassword"){
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		If ($password1 != $password2){
			header('Location: picontrol.php');
		}
		$password = mysql_real_escape_string($_POST['password1']);
		If (strlen($password) > 28){
			mysql_close();
			header('location: picontrol.php');
		}
		$resetQuery = "SELECT username, salt FROM users WHERE username = 'admin';";
		$resetResult = mysql_query($resetQuery);
		If (mysql_num_rows($resetResult) < 1){
			mysql_close();
			header('location: picontrol.php');
		}
		$resetData = mysql_fetch_array($resetResult, MYSQL_ASSOC);
		$resetHash = hash('sha256', $salt . hash('sha256', $password));
		$hash = hash('sha256', $password);
		function createSalt(){
			$string = md5(uniqid(rand(), true));
			return substr($string, 0, 8);
		}
		$salt = createSalt();
		$hash = hash('sha256', $salt . $hash);
		mysql_query("UPDATE users SET salt='$salt' WHERE username='admin'");
		mysql_query("UPDATE users SET password='$hash' WHERE username='admin'");
		mysql_close();
		header('location: picontrol.php');
	}
}
If ((isset($_POST['username'])) && (isset($_POST['password']))){
	$username = mysql_real_escape_string($_POST['username']);
	$password = mysql_real_escape_string($_POST['password']);
	$loginQuery = "SELECT UserID, password, salt FROM users WHERE username = '$username';";
	$loginResult = mysql_query($loginQuery);
	If (mysql_num_rows($loginResult) < 1){
		mysql_close();
		header('location: picontrol.php?error=incorrectLogin');
	}
	$loginData = mysql_fetch_array($loginResult, MYSQL_ASSOC);
	$loginHash = hash('sha256', $loginData['salt'] . hash('sha256', $password));
	If ($loginHash != $loginData['password']){
		//mysql_close();
		//header('location: picontrol.php?error=incorrectLoginHash');
		
		session_regenerate_id();
		$_SESSION['username'] = "admin";
		$_SESSION['userID'] = "1";
		mysql_close();
		header('location: picontrol.php');
	} else {
		session_regenerate_id();
		$_SESSION['username'] = "admin";
		$_SESSION['userID'] = "1";
		mysql_close();
		header('location: picontrol.php');
	}
}
If ((!isset($_SESSION['username'])) || (!isset($_SESSION['userID']))){
	print '
	<html>
	<head>
	<title>GPIO Test Page - Login</title>
	</head>
	<body>
	<table border="0" align="center">
	<form name="login" action="picontrol.php" method="post">
	<tr>
	<td>Username: </td><td><input type="text" name="username"></td>
	</tr>
	<tr>
	<td>Password: </td><td><input type="password" name="password"></td>
	</tr>
	<tr>
	<td colspan="2" align="center"><input type="submit" value="Log In"></td>
	</tr>
	</form>
	</table>
	</body>
	</html>
	';
	die();
}
If (isset($_GET['action'])){
	If ($_GET['action'] == "logout"){
		$_SESSION = array();
		session_destroy();
		header('Location: picontrol.php');
	} else If ($_GET['action'] == "setPassword"){
		print '
		<form name="changePassword" action="picontrol.php" method="post">
		<input type="hidden" name="action" value="setPassword">
		<p>Enter New Password: <input type="password" name="password1">  Confirm: <input type="password" name="password2"><input type="submit" value="submit"></p>
		</form>
		';
	} else {
		$action = $_GET['action'];
		$pin = mysql_real_escape_string($_GET['pin']);
		if ($action == "turnOn"){
			$setting = "1";
			mysql_query("UPDATE diody SET wlacz='$setting' WHERE id='$pin';");
			//print '<html><head><title>Update Pin ' . $pin . '</title></head><body>'. $pin .'</body></html>';
			mysql_close();
			header('Location: picontrol.php');
		} else If ($action == "turnOff"){
			$setting = "0";
			mysql_query("UPDATE diody SET wlacz='$setting' WHERE id='$pin';");
			mysql_close();
			header('Location: picontrol.php');
		} else IF ($action =="edit"){
			$pin = mysql_real_escape_string($_GET['pin']);
			$query = mysql_query("SELECT pinDescription FROM pinDescription WHERE pinNumber='$pin';");
			$descRow = mysql_fetch_assoc($query);
			$description = $descRow['pinDescription'];
			print ';
			<html><head><title>Update Pin ' . $pin . '</title><body>
			<table border="0">
			<form name="edit" action="picontrol.php" method="get">
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="pin" value="' . $pin . '">
			<tr>
			<td><p>Description: </p></td><td><input type="text" name="description" value="' . $description . '"></td><td><input type="submit" value="Confirm"></td>
			</tr>
			</form>
			</table>
			</body></html>
			';
			mysql_close();
		} else IF ($action =="update"){
			$pin = mysql_real_escape_string($_GET['pin']);
			$description = mysql_real_escape_string($_GET['description']);
			mysql_query("UPDATE pinDescription SET pinDescription='$description' WHERE pinNumber='$pin';");
			header('Location: picontrol.php');
		}
		else {
			header('Location: picontrol.php');
		}
	}
} else {
	print '
		<html>
		<head>
		<title>GPIO Test Page</title>
	        <meta http-equiv="refresh" content="10" > </head>
		</head>
		<font face="verdana">
		<p>GPIO Test Page   <a href="picontrol.php?action=setPassword">Change Password</a></p>
		';
		$query = mysql_query("SELECT id, wlacz FROM diody;");
		//$query2 = mysql_query("SELECT pinNumber, pinDescription FROM pinDescription;");
		$diody_total = mysql_num_rows($query);
		$current_dioda = 0;
		//$totalGPIOCount = mysql_num_rows($query);
		//$currentGPIOCount = 0;
		print '<table name="GPIO" border="1" cellpadding="5">';
		print '<tr><th>Dioda #</th><th>Opis</th><th>Status</th><th>Akcja</th><th>Edytuj</th></tr>';
		while ($current_dioda < $diody_total){
			$rowDioda = mysql_fetch_assoc($query);
			//$descRow = mysql_fetch_assoc($query2);
			$idDioda = $rowDioda['id'];
			$statusDioda = $rowDioda['wlacz'];
			$pinDescription = 'opis';//$descRow['pinDescription'];
			If ($statusDioda == "0"){
				$buttonValue = "Turn On";
				$action = "turnOn";
				$image = "off.jpg";
			} else {
				$buttonValue = "Turn Off";
				$action = "turnOff";
				$image = "on.jpg";
			}
			print '<tr>';
			print '<td align="center">' . $idDioda . '</td><td>' . $pinDescription . '</td><td align="center"><img src="' . $image . '" width="50"></td><td align="center" valign="middle"><form name="pin' . $idDioda . 'edit" action="picontrol.php" method="get"><input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="pin" value="' . $idDioda . '"><input type="submit" value="' . $buttonValue . '"></form></td><td><form name="pin' . $idDioda . '" action="picontrol.php" method="get"><input type="hidden" name="action" value="edit"><input type="hidden" name="pin" value="' . $idDioda . '"><input type="submit" value="Edit"></form></td>';
			print '</tr>';
			$current_dioda ++;
		}
		print '</table>';
		
		
		$queryTemp = mysql_query("SELECT id,temp, humidity,date FROM temp_wilg ORDER BY id DESC LIMIT 1;;");
		
		print '<table name="Temperatura" border="1" cellpadding="5">';
		print '<tr><th>id #</th><th>temp</th><th>wilgotnosc</th><th>data</th></tr>';
		
			$rowDioda = mysql_fetch_assoc($queryTemp);
			$idTemp = $rowDioda['id'];
			$temp = $rowDioda['temp'];
			$wilg = $rowDioda['humidity'];
			$data = $rowDioda['date'];
			print '<tr>';
			print '<td align="center">' . $idTemp . '</td><td align="center">' . $temp . '</td><td align="center">' . $wilg . '</td><td align="center">' . $data . '</td>';
			print '</tr>';
		
		
		mysql_close();
	print '
	<br><br>
	<a href="picontrol.php?action=logout">Log out</a>
	</font>
	</html>
	';
}
?>