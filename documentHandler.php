<?php
if(!isset($_REQUEST["mode"])){
	echo("HTTP/1.1 403 Forbidden");
	exit();
}else{
$mode = $_REQUEST["mode"];
//Eventually check user id for permission
$uid = $_REQUEST["uid"];
//Probably need to check page id as well
$pid = $_REQUEST["pid"];
mysql_connect("localhost","root","bn89dpy");
mysql_select_db("omnipad") or die("Unable to select database");

/*
Database structure
Document
	- pid:document#, title: title, cells:cell# list, permissions: permission uid's
Cells
	- cid:cell#, pid:document#, revision:revision#, lockuid:lock uid, data:data
*/

if ($mode==="createDocument"){ //Post
	$title = mysql_real_escape_string($_REQUEST["title"]);
	mysql_query("INSERT INTO Documents VALUES ('','$title','','$uid')");
	echo "INSERT INTO Documents VALUES ('','$title','','$uid')";
	echo (mysql_insert_id());
}elseif($mode==="addCell"){ //Post
	//checkPermission();
	//Add cell
	mysql_query("INSERT INTO Cells VALUES ('',$pid,'0', '-1', '')");
	$newCell=mysql_insert_id();

	//Update Document to include cell
	$result=mysql_query("SELECT cells FROM Documents WHERE pid='$pid'");
	$row=mysql_fetch_array($result);
	//var_dump($row);
	if ($row["cells"]!=""){
		foreach((explode(",", $row["cells"])) as $value){
			$cellsList[] = intval($value);
		}
		sort($cellsList);
		$cellsList[] = $newCell;
		$record = implode(",",$cellsList);
	}else{
		$record = $newCell;
	}

	mysql_query("UPDATE Documents SET cells='$record', permissions='$uid' WHERE pid='$pid'");
	
	echo($newCell);
}elseif($mode==="updateCell"){
	$cid = $_POST["cid"];
	$text = mysql_real_escape_string($_POST["text"]);
	$result=mysql_query("SELECT cells FROM Documents WHERE pid='$pid'");
	$row=mysql_fetch_array($result);
	$cellsList = (explode(",", $row["cells"]));
	if ($in_array($cid)){
		mysql_query("UPDATE Cells SET data='$text' WHERE cid='$cid'");
		echo ('sucess');
	}else{
		header("HTTP/1.1 404 Not Found");
	}
	
}elseif($mode==="pollChangedCells"){ //Get
	//checkPermission();
	$result=mysql_query("SELECT * FROM Cells WHERE pid='$pid'");
	$row=mysql_fetch_array($result);
	//A list of revision numbers, one for each cell
	$revision = json_decode($_GET["revision"]);
	$changed = array();
	foreach($row as $cell){
		$cid = $cell["cid"];
		//Check if new cell has been added, or if cell has been changed
		if (!array_key_exists($cid, $revision)||$cell["revision"]!=$revision[$cid]){
			$changed[] = $row;
		}
	}
	echo (json_encode($changed));
}else{
	exit();
}

function checkPermission(){
	/*$pid = $_GET["pid"];
	$result=mysql_query("SELECT * FROM Documents WHERE pid='$pid'");
	$row=mysql_fetch_array($result);*/
	//check to make sure document exists
	//Check for permissions
}

}
?>