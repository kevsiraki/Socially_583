<?php


session_start();

require_once "config.php";

$sql3 = "SELECT * FROM users WHERE username = '" . $_SESSION['username'] . "' ";
$result3 = mysqli_query($link, $sql3);
$basics = mysqli_fetch_assoc($result3);

if ( (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) ) {
    header("location: login.php"); 
    exit;
}

if(isset($_POST['formSubmit']) && $_POST['del'] == 'Yes') {
	mysqli_query($link, "DELETE FROM hobbies WHERE username = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM follows WHERE leadername = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM follows WHERE followername = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM comments WHERE posted_by = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM blogstags WHERE blogid IN (SELECT blogid FROM blogs WHERE created_by = '" . trim($_SESSION["username"]) . "') ;");
	mysqli_query($link, "DELETE FROM blogs WHERE created_by = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM users WHERE username = '" . trim($_SESSION["username"]) . "';");
	header("location: logout.php");
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" http-equiv="refresh" content="300;url=logout.php"/> 
		<title>Account Settings</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<style>
			body{ width: 360px; padding: 20px; margin-left: auto;
			margin-right: auto; margin-top:10%; font: 14px sans-serif; background-color: #e8f3fd;}
			
		</style>	
	</head>	
	<body>
	<h1>Account Settings</h1><br><br>

	<br>
	<form  method="post">
		<input type="checkbox" name="del" value="Yes" > <b>Delete Account</b></input>
		<input type="submit" name="formSubmit" value="Are You Sure?" class="btn-secondary btn-sm" />
	</form>
	<br><br><br><br><br>
	<p>
		<a href="logout.php" class="btn btn-secondary" value="Submit"><b>Sign Out</b></a>
		<a href="reset-password.php" class="btn btn-secondary"><b>Reset Your Password</b></a>
	</p>
  </body>
</html>