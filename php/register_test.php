<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

print("<p>in it</p>");

// retrieve user input
$email = (isset($_POST["email"])) ? $_POST["email"] : "";
$pass = (isset($_POST["pass"])) ? $_POST["pass"] : "";
$passRep = (isset($_POST["repPass"])) ? $_POST["repPass"] : "";

// get salts
$emailSalt = "";
$passSalt = "";
$con = new mysqli("fall-2014.cs.utexas.edu", "jking", "4zPjLvoHWu", "cs329e_jking");
if($con->connect_errno)
{
	printErrMsg($con->connect_error);
	return;
}
$query = $con->prepare("SELECT * FROM ADMIN WHERE property='email_salt' OR property='pw_salt';");
if(!$query)
{
	printErrMsg($con->error);
	return;
}
if(!$query->execute())
{
	printErrMsg($query->error);
	return;
}
if(!$query->bind_result($property, $value))
{
	printErrMsg($query->error);
	return;
}
switch($query->fetch())
{
	case false:
		printErrMsg($query->error);
		return;
		break;
	case null:
		printErrMsg("No results returned by query when result was expected.");
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
	printErrMsg("Unexpected results returned by query, can not continue.");
	return;
}
switch($query->fetch())
{
	case false:
		printErrMsg($query->error);
		return;
		break;
	case null:
		printErrMsg("No results returned by query when result was expected.");
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
	printErrMsg("Unexpcted results returned by query, can not continue.");
	return;
}
$query->free_result();
if(!$query->close())
{
	printErrMsg($query->error);
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
$validPass = validatePassword($pass);
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
$validPassRep = validatePassword($passRep) && $pass === $passRep;

// insert new user if credentials are valid
if($validEmail && $validPass && $validPassRep)
{
	$emailHash = hash("sha512", $emailSalt . $email);
	$passHash = hash("sha512", $passSalt . $pass);
	$query = $con->prepare("INSERT INTO USERS VALUES(?, ?);");
	if(!$query)
	{
		printErrMsg($con->error);
		return;
	}
	if(!$query->bind_param("ss", $emailHash, $passHash))
	{
		printErrMsg($query->error);
		return;
	}
	if(!$query->execute())
	{
		printErrMsg($query->error);
		return;
	}
	if(!$query->close())
	{
		printErrMsg($query->error);
		return;
	}
	print("<p>Successfully added user ($email, $pass) to database...</p>");
}
if(!$con->close())
{
	printErrMsg($con->error);
	return;
}

function validateEmail($email, $emailSalt)
{
	// confirm that email is valid (there are known bugs but they are conservative in nature, so it's no a big issue at
	// this time)
	if(filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		// confirm that email is not in database
		// should login credentials be saved in a different file?
		$emailHash = hash("sha512", $emailSalt . $email);
		$con = new mysqli("fall-2014.cs.utexas.edu", "jking", "4zPjLvoHWu", "cs329e_jking");
		if($con->connect_errno)
		{
			printErrMsg($con->connect_error);
			return false;
		}
		$query = $con->prepare("SELECT COUNT(*) FROM USERS WHERE emailHash=?;");
		if(!$query)
		{
			printErrMsg($con->error);
			return false;
		}
		if(!$query->bind_param("s", $emailHash))
		{
			printErrMsg($query->error);
			return false;
		}
		if(!$query->execute())
		{
			printErrMsg($query->error);
			return false;
		}
		if(!$query->bind_result($count))
		{
			printErrMsg($query->error);
			return false;
		}
		switch($query->fetch())
		{
			case false:
				printErrMsg($query->error);
				return false;
				break;
			case null:
				printErrMsg("No results returned by query when result was expected.");
				return false;
				break;
		}
		$query->free_result();
		if(!$query->close())
		{
			printErrMsg($query->error);
			return false;
		}
		if(!$con->close())
		{
			printErrMsg($con->error);
			return false;
		}
		return $count === 0;
	}
	return false;
}

function validatePassword($pass)
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
		return true;
	}
	return false;
}

function printErrMsg($msg)
{
	print <<<EOB
<!DOCTYPE html>
<html>
	<head>
		<title>Registration Test - Error</title>
	</head>
	<body>
		<pre>{$msg}</pre>
	</body>
</html>
EOB;
}

?>