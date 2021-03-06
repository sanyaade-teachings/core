<?
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

//Gibbon system-wide includes
include "../../functions.php" ;
include "../../config.php" ;

//Module includes
include "./moduleFunctions.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonPlannerEntryID=$_GET["gibbonPlannerEntryID"] ;
$gibbonPlannerEntryHomeworkID=$_GET["gibbonPlannerEntryHomeworkID"] ;
$date=$_GET["date"] ;
$gibbonCourseClassID=$_GET["gibbonCourseClassID"] ;
$viewBy=$_GET["viewBy"] ;
$subView=$_GET["subView"] ;
$search=NULL ;
if (isset($_POST["search"])) {
	$search=$_POST["search"] ;
}
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Planner/planner_view_full.php&date=$date&viewBy=$viewBy&subView=$subView&gibbonCourseClassID=$gibbonCourseClassID&gibbonPlannerEntryID=$gibbonPlannerEntryID&search=$search" ;

if (isActionAccessible($guid, $connection2, "/modules/Planner/planner_view_full.php")==FALSE) {
	//Fail 0
	$URL=$URL . "&deleteReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	//Check if planner specified
	if ($gibbonPlannerEntryID=="" OR $gibbonPlannerEntryHomeworkID=="") {
		//Fail1
		$URL=$URL . "&deleteReturn=fail1" ;
		header("Location: {$URL}");
	}
	else {
		try {
			$data=array("gibbonPlannerEntryID"=>$gibbonPlannerEntryID); 
			$sql="SELECT * FROM gibbonPlannerEntry WHERE gibbonPlannerEntryID=:gibbonPlannerEntryID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail2
			$URL=$URL . "&deleteReturn=fail2" ;
			header("Location: {$URL}");
			BREAK ;
		}

		if ($result->rowCount()!=1) {
			//Fail 2
			$URL=$URL . "&deleteReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {	
			//INSERT
			try {
				$data=array("gibbonPlannerEntryHomeworkID"=>$gibbonPlannerEntryHomeworkID); 
				$sql="DELETE FROM gibbonPlannerEntryHomework WHERE gibbonPlannerEntryHomeworkID=:gibbonPlannerEntryHomeworkID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL=$URL . "&deleteReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}
			
			$URL=$URL . "&deleteReturn=success0" ;
			//Success 0
			header("Location: {$URL}");
		}
	}
}
?>