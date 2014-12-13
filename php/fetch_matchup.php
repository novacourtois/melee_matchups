<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

print("<p>in it</p>");

$character = "Falco";
$opponent = "Falco";

$con = new mysqli("fall-2014.cs.utexas.edu", "jking", "4zPjLvoHWu", "cs329e_jking");
if($con->connect_errno)
{
	printErrMsg($con->connect_error);
	return;
}
$query = $con->prepare("SELECT * FROM MATCHUP_DATA WHERE characterName=? AND opponentName=?;");
if(!$query)
{
	printErrMsg($con->error);
	return;
}
if(!$query->bind_param("ss", $character, $opponent))
{
	printErrMsg($query->error);
	return false;
}
if(!$query->execute())
{
	printErrMsg($query->error);
	return;
}
if(!$query->bind_result($characterName, $opponentName, $winPercentage, $characterTips, $opponentTips))
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
$arr = array("character" => $characterName, "opponent" => $opponentName, "percentage" => $winPercentage,
	"characterTips" => $characterTips, "opponentTips" => $opponentTips);
$jsonStr = json_encode($arr);
return $jsonStr;

function printErrMsg($msg)
{
	// need change to output client-friendly error page
	print <<<EOB
<!DOCTYPE html>
<html>
	<head>
		<title>Login Test - Error</title>
	</head>
	<body>
		<p>Error:</p>
		<pre>{$msg}</pre>
	</body>
</html>
EOB;
}

?>