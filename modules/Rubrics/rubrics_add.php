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

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Rubrics/rubrics_add.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Get action with highest precendence
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
	if ($highestAction==FALSE) {
		print "<div class='error'>" ;
		print "The highest grouped action cannot be determined." ;
		print "</div>" ;
	}
	else {
		if ($highestAction!="Manage Rubrics_viewEditAll" AND $highestAction!="Manage Rubrics_viewAllEditLearningArea") {
			print "<div class='error'>" ;
				print "You do not have access to this action." ;
			print "</div>" ;
		}
		else {
			//Proceed!
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/rubrics.php'>Manage Rubrics</a> > </div><div class='trailEnd'>Add Rubric</div>" ;
			print "</div>" ;
			
			if (isset($_GET["addReturn"])) { $addReturn=$_GET["addReturn"] ; } else { $addReturn="" ; }
			$addReturnMessage ="" ;
			$class="error" ;
			if (!($addReturn=="")) {
				if ($addReturn=="fail0") {
					$addReturnMessage ="Add failed because you do not have access to this action." ;	
				}
				else if ($addReturn=="fail2") {
					$addReturnMessage ="Add failed due to a database error." ;	
				}
				else if ($addReturn=="fail3") {
					$addReturnMessage ="Add failed because your inputs were invalid." ;	
				}
				else if ($addReturn=="fail4") {
					$addReturnMessage ="Add failed because the selected person is already registered." ;	
				}
				else if ($addReturn=="fail5") {
					$addReturnMessage ="Add succeeded, but there were problems uploading one or more attachments." ;	
				}
				else if ($addReturn=="success0") {
					$addReturnMessage ="Add was successful. You can add another record if you wish." ;	
					$class="success" ;
				}
				print "<div class='$class'>" ;
					print $addReturnMessage;
				print "</div>" ;
			} 
			
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/rubrics_addProcess.php" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr class='break'>
						<td colspan=2>
							<h3>Rubric Basics</h3>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Scope *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<?
							if ($highestAction=="Manage Rubrics_viewEditAll") {
								?>
								<select name="scope" id="scope" style="width: 302px">
									<option value="Please select...">Please select...</option>
									<option value="School">School</option>
									<option value="Learning Area">Learning Area</option>
								</select>
								<script type="text/javascript">
									var scope=new LiveValidation('scope');
									scope.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
								 </script>
								 <?
							}
							else if ($highestAction=="Manage Rubrics_viewAllEditLearningArea") {
								?>
								<input readonly name="scope" id="scope" value="Learning Area" type="text" style="width: 300px">
								<?
							}
							?>
						</td>
					</tr>
					
					
					<?
					if ($highestAction=="Manage Rubrics_viewEditAll") {
						?>
						<script type="text/javascript">
							$(document).ready(function(){
								$("#learningAreaRow").css("display","none");
								
								$("#scope").change(function(){
									if ($('#scope option:selected').val() == "Learning Area" ) {
										$("#learningAreaRow").slideDown("fast", $("#learningAreaRow").css("display","table-row")); 
										gibbonDepartmentID.enable();
									}
									else {
										$("#learningAreaRow").css("display","none");
										gibbonDepartmentID.disable();
									}
								 });
							});
						</script>
						<?
					}
					?>
					<tr id='learningAreaRow'>
						<td> 
							<b>Learning Area *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="gibbonDepartmentID" id="gibbonDepartmentID" style="width: 302px">
								<option value="Please select...">Please select...</option>
								<?
								try {
									if ($highestAction=="Manage Rubrics_viewEditAll") {
										$dataSelect=array(); 
										$sqlSelect="SELECT * FROM gibbonDepartment WHERE type='Learning Area' ORDER BY name" ;
									}
									else if ($highestAction=="Manage Rubrics_viewAllEditLearningArea") {
										$dataSelect=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
										$sqlSelect="SELECT * FROM gibbonDepartment JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE gibbonPersonID=:gibbonPersonID AND (role='Coordinator' OR role='Teacher (Curriculum)') AND type='Learning Area' ORDER BY name" ;
									}
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { }
								while ($rowSelect=$resultSelect->fetch()) {
									print "<option value='" . $rowSelect["gibbonDepartmentID"] . "'>" . $rowSelect["name"] . "</option>" ;
								}
								?>
							</select>
							<script type="text/javascript">
								var gibbonDepartmentID=new LiveValidation('gibbonDepartmentID');
								gibbonDepartmentID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
								<?
								if ($highestAction=="Manage Rubrics_viewEditAll") {
									print "gibbonDepartmentID.disable();" ;
								}
								?>
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Name *</b><br/>
						</td>
						<td class="right">
							<input name="name" id="name" maxlength=50 value="" type="text" style="width: 300px">
							<script type="text/javascript">
								var name=new LiveValidation('name');
								name.add(Validate.Presence);
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Active *</b><br/>
							<span style="font-size: 90%"><i></i></span>
						</td>
						<td class="right">
							<select name="active" id="active" style="width: 302px">
								<option value="Y">Y</option>
								<option value="N">N</option>
							</select>
						</td>
					</tr>
					
					<tr>
						<td> 
							<b>Category</b><br/>
						</td>
						<td class="right">
							<input name="category" id="category" maxlength=100 value="" type="text" style="width: 300px">
							<script type="text/javascript">
								$(function() {
									var availableTags=[
										<?
										try {
											$dataAuto=array(); 
											$sqlAuto="SELECT DISTINCT category FROM gibbonRubric ORDER BY category" ;
											$resultAuto=$connection2->prepare($sqlAuto);
											$resultAuto->execute($dataAuto);
										}
										catch(PDOException $e) { }
										while ($rowAuto=$resultAuto->fetch()) {
											print "\"" . $rowAuto["category"] . "\", " ;
										}
										?>
									];
									$( "#category" ).autocomplete({source: availableTags});
								});
							</script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Description</b><br/>
						</td>
						<td class="right">
							<textarea name='description' id='description' rows=5 style='width: 300px'></textarea>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Year Groups</b><br/>
							<span style="font-size: 90%"><i>Relevant student year groups<br/></i></span>
						</td>
						<td class="right">
							<? 
							$yearGroups=getYearGroups($connection2) ;
							if ($yearGroups=="") {
								print "<i>No year groups available.</i>" ;
							}
							else {
								for ($i=0; $i<count($yearGroups); $i=$i+2) {
									$checked="checked " ;
									print $yearGroups[($i+1)] . " <input $checked type='checkbox' name='gibbonYearGroupIDCheck" . ($i)/2 . "'><br/>" ; 
									print "<input type='hidden' name='gibbonYearGroupID" . ($i)/2 . "' value='" . $yearGroups[$i] . "'>" ;
								}
							}
							?>
							<input type="hidden" name="count" value="<? print (count($yearGroups))/2 ?>">
						</td>
					</tr>
					<tr>
						<td> 
							<b>Grading Scale</b><br/>
							<span style="font-size: 90%"><i>Link columns to grades on a scale?</i></span>
						</td>
						<td class="right">
							<select name="gibbonScaleID" id="gibbonScaleID" style="width: 302px">
								<?
								print "<option value=''></option>" ;
								try {
									$dataSelect=array(); 
									$sqlSelect="SELECT * FROM gibbonScale WHERE (active='Y') ORDER BY name" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { 
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								while ($rowSelect=$resultSelect->fetch()) {
									if ($row["gibbonScaleID"]==$rowSelect["gibbonScaleID"]) {
										print "<option selected value='" . $rowSelect["gibbonScaleID"] . "'>" . htmlPrep($rowSelect["name"]) . "</option>" ;
									}
									else {
										print "<option value='" . $rowSelect["gibbonScaleID"] . "'>" . htmlPrep($rowSelect["name"]) . "</option>" ;
									}
								}
								?>				
							</select>
						</td>
					</tr>
								
					<tr class='break'>
						<td colspan=2>
							<h3>Rubric Design</h3>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Initial Rows *</b><br/>
							<span style="font-size: 90%"><i>Rows store assessment strands.</i></span>
						</td>
						<td class="right">
							<select name="rows" id="rows" style="width: 302px">
								<?
								for ($i=1; $i<=10; $i++) {
									print "<option value='$i'>$i</option>" ;
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Initial Columns *</b><br/>
							<span style="font-size: 90%"><i>Columns store assessment levels.</i></span>
						</td>
						<td class="right">
							<select name="columns" id="columns" style="width: 302px">
								<?
								for ($i=1; $i<=10; $i++) {
									print "<option value='$i'>$i</option>" ;
								}
								?>
							</select>
						</td>
					</tr>
					
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
		}
	}
}
?>