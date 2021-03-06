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

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/User Admin/medicalForm_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/User Admin/medicalForm_manage.php'>Manage Medical Forms</a> > </div><div class='trailEnd'>Edit Medical Form</div>" ;
	print "</div>" ;
	
	if (isset($_GET["updateReturn"])) { $updateReturn=$_GET["updateReturn"] ; } else { $updateReturn="" ; }
	$updateReturnMessage ="" ;
	$class="error" ;
	if (!($updateReturn=="")) {
		if ($updateReturn=="fail0") {
			$updateReturnMessage ="Update failed because you do not have access to this action." ;	
		}
		else if ($updateReturn=="fail1") {
			$updateReturnMessage ="Update failed because a required parameter was not set." ;	
		}
		else if ($updateReturn=="fail2") {
			$updateReturnMessage ="Update failed due to a database error." ;	
		}
		else if ($updateReturn=="fail3") {
			$updateReturnMessage ="Update failed because your inputs were invalid." ;	
		}
		else if ($updateReturn=="success0") {
			$updateReturnMessage ="Update was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $updateReturnMessage;
		print "</div>" ;
	} 
	
	if (isset($_GET["deleteReturn"])) { $deleteReturn=$_GET["deleteReturn"] ; } else { $deleteReturn="" ; }
	$deleteReturnMessage ="" ;
	$class="error" ;
	if (!($deleteReturn=="")) {
		if ($deleteReturn=="success0") {
			$deleteReturnMessage ="Delete was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $deleteReturnMessage;
		print "</div>" ;
	} 
	
	//Check if person medical specified
	$gibbonPersonMedicalID=$_GET["gibbonPersonMedicalID"] ;
	$search=NULL ;
	if (isset($_GET["search"])) {
		$search=$_GET["search"] ;
	}
	if ($gibbonPersonMedicalID=="") {
		print "<div class='error'>" ;
			print "You have not specified a medical form" ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("gibbonPersonMedicalID"=>$gibbonPersonMedicalID); 
			$sql="SELECT * FROM gibbonPersonMedical WHERE gibbonPersonMedicalID=:gibbonPersonMedicalID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}

		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print "The specified medical form cannot be found." ;
			print "</div>" ;
		}
		else {
			//Let's go!
			$row=$result->fetch() ;
			
			if ($search!="") {
				print "<div class='linkTop'>" ;
					print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/User Admin/medicalForm_manage.php&search=$search'>Back to Search Results</a>" ;
				print "</div>" ;
			}
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/medicalForm_manage_editProcess.php?gibbonPersonMedicalID=" . $gibbonPersonMedicalID . "&search=$search" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td> 
							<b>Person *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
						</td>
						<td class="right">
							<?
							try {
								$dataSelect=array("gibbonPersonID"=>$row["gibbonPersonID"]); 
								$sqlSelect="SELECT surname, preferredName FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID" ;
								$resultSelect=$connection2->prepare($sqlSelect);
								$resultSelect->execute($dataSelect);
							}
							catch(PDOException $e) { }
							$rowSelect=$resultSelect->fetch() ;
							?>	
							<input readonly name="name" id="name" maxlength=255 value="<? print formatName("", htmlPrep($rowSelect["preferredName"]), htmlPrep($rowSelect["surname"]), "Student") ; ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td> 
							<b>Blood Type</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select style="width: 302px" name="bloodType">
								<option <? if ($row["bloodType"]=="") {print "selected ";}?>value=""></option>
								<option <? if ($row["bloodType"]=="O+") {print "selected ";}?>value="O+">O+</option>
								<option <? if ($row["bloodType"]=="A+") {print "selected ";}?>value="A+">A+</option>
								<option <? if ($row["bloodType"]=="B+") {print "selected ";}?>value="B+">B+</option>
								<option <? if ($row["bloodType"]=="AB+") {print "selected ";}?>value="AB+">AB+</option>
								<option <? if ($row["bloodType"]=="O-") {print "selected ";}?>value="O-">O-</option>
								<option <? if ($row["bloodType"]=="A-") {print "selected ";}?>value="A-">A-</option>
								<option <? if ($row["bloodType"]=="B-") {print "selected ";}?>value="B-">B-</option>
								<option <? if ($row["bloodType"]=="AB-") {print "selected ";}?>value="AB-">AB-</option>
							</select>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Long-Term Medication?</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select style="width: 302px" name="longTermMedication">
								<option <? if ($row["longTermMedication"]=="") {print "selected ";}?>value=""></option>
								<option <? if ($row["longTermMedication"]=="Y") {print "selected ";}?>value="Y">Y</option>
								<option <? if ($row["longTermMedication"]=="N") {print "selected ";}?>value="N">N</option>
							</select>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Medication Details</b><br/>
							<span style="font-size: 90%"><i>1000 character limit</i></span>
						</td>
						<td class="right">
							<textarea name="longTermMedicationDetails" id="longTermMedicationDetails" rows=8 style="width: 300px"><? print $row["longTermMedicationDetails"] ?></textarea>
							<script type="text/javascript">
								var longTermMedicationDetails=new LiveValidation('longTermMedicationDetails');
								longTermMedicationDetails.add( Validate.Length, { maximum: 1000 } );
							 </script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Tetanus Within Last 10 Years?</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select style="width: 302px" name="tetanusWithin10Years">
								<option <? if ($row["tetanusWithin10Years"]=="") {print "selected ";}?>value=""></option>
								<option <? if ($row["tetanusWithin10Years"]=="Y") {print "selected ";}?>value="Y">Y</option>
								<option <? if ($row["tetanusWithin10Years"]=="N") {print "selected ";}?>value="N">N</option>
							</select>
						</td>
					</tr>						
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="gibbonPersonMedicalID" value="<? print $row["gibbonPersonMedicalID"] ?>">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
			
			print "<h2>" ;
			print "Medical Conditions" ;
			print "</h2>" ;
			
			try {
				$data=array("gibbonPersonMedicalID"=>$gibbonPersonMedicalID); 
				$sql="SELECT gibbonPersonMedicalCondition.*, gibbonAlertLevel.name AS risk FROM gibbonPersonMedicalCondition JOIN gibbonAlertLevel ON (gibbonPersonMedicalCondition.gibbonAlertLevelID=gibbonAlertLevel.gibbonAlertLevelID) WHERE gibbonPersonMedicalID=:gibbonPersonMedicalID ORDER BY gibbonPersonMedicalCondition.name" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			
			print "<div class='linkTop'>" ;
			print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/medicalForm_manage_condition_add.php&gibbonPersonMedicalID=" . $row["gibbonPersonMedicalID"] . "&search=$search'><img title='New' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.gif'/></a>" ;
			print "</div>" ;
			
			if ($result->rowCount()<1) {
				print "<div class='error'>" ;
				print "There are no medical conditions to display." ;
				print "</div>" ;
			}
			else {
				print "<table cellspacing='0' style='width: 100%'>" ;
					print "<tr class='head'>" ;
						print "<th>" ;
							print "Name" ;
						print "</th>" ;
						print "<th>" ;
							print "Risk" ;
						print "</th>" ;
						print "<th>" ;
							print "Details" ;
						print "</th>" ;
						print "<th>" ;
							print "Medication" ;
						print "</th>" ;
						print "<th>" ;
							print "Comment" ;
						print "</th>" ;
						print "<th>" ;
							print "Actions" ;
						print "</th>" ;
					print "</tr>" ;
					
					$count=0;
					$rowNum="odd" ;
					while ($row=$result->fetch()) {
						if ($count%2==0) {
							$rowNum="even" ;
						}
						else {
							$rowNum="odd" ;
						}
						$count++ ;
						
						//COLOR ROW BY STATUS!
						print "<tr class=$rowNum>" ;
							print "<td>" ;
								print $row["name"] ;
							print "</td>" ;
							print "<td>" ;
								print $row["risk"] ;
							print "</td>" ;
							print "<td>" ;
								if ($row["triggers"]!="") {
									print "<b>Triggers:</b> " . $row["triggers"] . "<br/>" ;
								}
								if ($row["reaction"]!="") {
									print "<b>Reaction:</b> " . $row["reaction"] . "<br/>" ;
								}
								if ($row["response"]!="") {
									print "<b>Response:</b> " . $row["response"] . "<br/>" ;
								}
								if ($row["lastEpisode"]!="") {
									print "<b>Last Episode:</b> " . dateConvertBack($row["lastEpisode"]) . "<br/>" ;
								}
								if ($row["lastEpisodeTreatment"]!="") {
									print "<b>Last Episode Treatment:</b> " . $row["lastEpisodeTreatment"] . "<br/>" ;
								}
							print "</td>" ;
							print "<td>" ;
								print $row["medication"] ;
							print "</td>" ;
							print "<td>" ;
								print $row["comment"] ;
							print "</td>" ;
							print "<td>" ;
								print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/medicalForm_manage_condition_edit.php&gibbonPersonMedicalID=" . $row["gibbonPersonMedicalID"] . "&gibbonPersonMedicalConditionID=" . $row["gibbonPersonMedicalConditionID"] . "&search=$search'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
								print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/medicalForm_manage_condition_delete.php&gibbonPersonMedicalID=" . $row["gibbonPersonMedicalID"] . "&gibbonPersonMedicalConditionID=" . $row["gibbonPersonMedicalConditionID"] . "&search=$search'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a>" ;
							print "</td>" ;
						print "</tr>" ;
					}
				print "</table>" ;
			}
		}
	}
}
?>