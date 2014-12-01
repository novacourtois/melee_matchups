<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);
print("<p>in it</p>");

// retrieve user input
$email = (isset($_POST["email"])) ? $_POST["email"] : "";
$pass = (isset($_POST["pass"])) ? $_POST["pass"] : "";

// get salts
$emailSalt = "";
$passSalt = "";

$con = new mysqli("fall-2014.cs.utexas.edu", "jking", /*password*/, "cs329e_jking");
if($con->connect_errno)
{
	$msg = "Failed to establish MySQL connection... Error message:\n" . $con->connect_error;
	printErrMsg($msg);
	return;
}

$query = $con->prepare("SELECT * FROM ADMIN WHERE property='email_salt' OR property='pw_salt';");
if(!$query)
{
	$msg = "Failed to prepare query... Error message:\n" . $con->error;
	printErrMsg($msg);
	return;
}
if(!$query->execute())
{
	$msg = "Failed to execute query... Error message:\n" . $query->error;
	printErrMsg($msg);
	return;
}
if(!$query->bind_result($property, $value))
{
	$msg = "Failed to bind query result... Error message:\n" . $query->error;
	printErrMsg($msg);
	return;
}
switch($query->fetch())
{
	case false:
		$msg = "Failed to fetch result... Error message:\n" . $query->error;
		printErrMsg($msg);
		return;
		break;
	case null:
		$msg = "Error: no results returned when result was expected...";
		printErrMsg($msg);
		return;
		break;
}
if($property === "email_salt")
{
	$emailSalt = $value;
}
elseif($property === "pw_salt")
{
	$passSalt = $value;
}
else
{
	$msg = "Unexpected results were returned by the query... Can not continue...\n";
	printErrMsg($msg);
	return;
}
switch($query->fetch())
{
	case false:
		$msg = "Failed to fetch result... Error message:\n" . $query->error;
		printErrMsg($msg);
		return;
		break;
	case null:
		$msg = "Error: no results returned when result was expected...";
		printErrMsg($msg);
		return;
		break;
}
if($property === "email_salt")
{
	$emailSalt = $value;
}
elseif($property === "pw_salt")
{
	$passSalt = $value;
}
else
{
	$msg = "Unexpected results were returned by the query... Can not continue...\n";
	printErrMsg($msg);
	return;
}
$query->free_result();
if(!$query->close())
{
	$msg = "Failed to close query... Error message:\n" . $query->error;
	printErrMsg($msg);
	return;
}
if(!$con->close())
{
	$msg = "Failed to close connection... Error message:\n" . $con->error;
	printErrMsg($msg);
	return;
}

// validate credentials
print("<p>$email & $pass</p>");
$validEmail = validateEmail($email, $emailSalt);
if($validEmail)
{
	print("<p>good email</p>");
}
else
{
	print("<p>bad email</p>");
	return;
}
$validPass = validatePassword($email, $emailSalt, $pass, $passSalt);
if($validPass)
{
	print("<p>good password</p>");
}
else
{
	print("<p>bad password</p>");
	return;
}
print("<p>good credentials</p>");

function validateEmail($email, $emailSalt)
{
	// confirm that email is valid (there are known bugs but they are conservative in nature, so it's no a bit issue at
	// this time)
	if(filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		// confirm that email is in database
		// should login credentials be saved in a different file?
		$emailHash = hash("sha512", $emailSalt . $email);
		$con = new mysqli("fall-2014.cs.utexas.edu", "jking", /*password*/, "cs329e_jking");
		if($con->connect_errno)
		{
			$msg = "Failed to establish MySQL connection... Error message:\n" . $con->connect_error;
			printErrMsg($msg);
			return;
		}
		$query = $con->prepare("SELECT COUNT(*) FROM USERS WHERE emailHash=?;");
		if(!$query)
		{
			$msg = "Failed to prepare query... Error message:\n" . $con->error;
			printErrMsg($msg);
			return;
		}
		if(!$query->bind_param("s", $emailHash))
		{
			$msg = "Failed to bind query parameters... Error message:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		if(!$query->execute())
		{
			$msg = "Failed to execute query... Error messages:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		if(!$query->bind_result($count))
		{
			$msg = "Failed to bind query result... Error message:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		switch($query->fetch())
		{
			case false:
				$msg = "Failed to fetch result... Error message:\n" . $query->error;
				printErrMsg($msg);
				return;
				break;
			case null:
				$msg = "Error: no results returned when result was expected...";
				printErrMsg($msg);
				return;
				break;
		}
		$query->free_result();
		if(!$query->close())
		{
			$msg = "Failed to close query... Error message:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		if(!$con->close())
		{
			$msg = "Failed to close connection... Error message:\n" . $con->error;
			printErrMsg($msg);
			return;
		}
		return $count === 1;
	}
	return false;
}

function validatePassword($email, $emailSalt, $pass, $passSalt)
{
	// a valid password is between 8 and 32 characters and contains at least 3 of a lower case letter, an upper case
	// letter, a number, a special character from the set {_, @, #, !, ?, <, >, .}
	// regex matches need to be tested
	$hasUpper = preg_match("/^[\d\w_@#!\?<>\.]*[A-Z]+[\d\w_@#!\?<>\.]*$/", $pass);
	$hasLower = preg_match("/^[\d\w_@#!\?<>\.]*[a-z]+[\d\w_@#!\?<>\.]*$/", $pass);
	$hasNumber = preg_match("/^[\d\w_@#!\?<>\.]*[0-9]+[\d\w_@#!\?<>\.]*$/", $pass);
	$hasSpecial = preg_match("/^[\d\w_@#!\?<>\.]*[_@#!\?<>\.]+[\d\w_@#!\?<>\.]*$/", $pass);
	$numPassed = 0;
	if($hasUpper)
	{
		$numPassed++;
	}
	if($hasLower)
	{
		$numPassed++;
	}
	if($hasNumber)
	{
		$numPassed++;
	}
	if($hasSpecial)
	{
		$numPassed++;
	}
	if($numPassed >= 3 && strlen($pass) >= 8 && strlen($pass) <= 32)
	{
		// confirm that password matches validated email in database
		$emailHash = hash("sha512", $emailSalt . $email);
		$passHash = hash("sha512", $passSalt . $pass);
		$con = new mysqli("fall-2014.cs.utexas.edu", "jking", /*password*/, "cs329e_jking");
		if($con->connect_errno)
		{
			$msg = "Failed to establish MySQL connection... Error message:\n" . $con->connect_error;
			printErrMsg($msg);
			return;
		}
		$query = $con->prepare("SELECT COUNT(*) FROM USERS WHERE emailHash=? AND pwHash=?;");
		if(!$query)
		{
			$msg = "Failed to prepare query... Error message:\n" . $con->error;
			printErrMsg($msg);
			return;
		}
		if(!$query->bind_param("ss", $emailHash, $passHash))
		{
			$msg = "Failed to bind query parameters... Error message:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		if(!$query->execute())
		{
			$msg = "Failed to execute query... Error messages:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		if(!$query->bind_result($count))
		{
			$msg = "Failed to bind query result... Error message:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		switch($query->fetch())
		{
			case false:
				$msg = "Failed to fetch result... Error message:\n" . $query->error;
				printErrMsg($msg);
				return;
				break;
			case null:
				$msg = "Error: no results returned when result was expected...";
				printErrMsg($msg);
				return;
				break;
		}
		$query->free_result();
		if(!$query->close())
		{
			$msg = "Failed to close query... Error message:\n" . $query->error;
			printErrMsg($msg);
			return;
		}
		if(!$con->close())
		{
			$msg = "Failed to close connection... Error message:\n" . $con->error;
			printErrMsg($msg);
			return;
		}
		return $count === 1;
	}
	return false;
}

function printErrMsg($msg)
{
	print <<<EOB
<!DOCTYPE html>
<html>
	<head>
		<title>Login Test - Error</title>
	</head>
	<body>
		<pre>{$msg}</pre>
	</body>
</html>
EOB;
}

?>
