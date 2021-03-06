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

if (isActionAccessible($guid, $connection2, "/modules/Planner/planner_view_full_submit_edit.php")==FALSE) {
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
		$viewBy=$_GET["viewBy"] ;
		$subView=$_GET["subView"] ;
		if ($viewBy!="date" AND $viewBy!="class") {
			$viewBy="date" ;
		}
		if ($viewBy=="date") {
			$date=$_GET["date"] ;
			if ($_GET["dateHuman"]!="") {
				$date=dateConvert($_GET["dateHuman"]) ;
			}
			if ($date=="") {
				$date=date("Y-m-d");
			}
			list($dateYear, $dateMonth, $dateDay)=explode('-', $date);
			$dateStamp=mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);	
		}
		else if ($viewBy=="class") {
			$class=NULL ;
			if (isset($_GET["class"])) {
				$class=$_GET["class"] ;
			}
			$gibbonCourseClassID=$_GET["gibbonCourseClassID"] ;
		}
			
		//Get class variable
		$gibbonPlannerEntryID=$_GET["gibbonPlannerEntryID"] ;
		
		if ($gibbonPlannerEntryID=="") {
			print "<div class='warning'>" ;
				print "Lesson has not been specified ." ;
			print "</div>" ;
		}
		//Check existence of and access to this class.
		else {
			try {
				if ($highestAction=="Lesson Planner_viewAllEditMyClasses" ) {
					$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "gibbonPlannerEntryID"=>$gibbonPlannerEntryID, "date"=>$date, "gibbonPersonID2"=>$_SESSION[$guid]["gibbonPersonID"], "gibbonPlannerEntryID2"=>$gibbonPlannerEntryID); 
					$sql="(SELECT gibbonPlannerEntry.gibbonPlannerEntryID, gibbonCourseClass.gibbonCourseClassID, gibbonUnitID, gibbonHookID, gibbonPlannerEntry.gibbonCourseClassID, gibbonPlannerEntry.name, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, gibbonPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM gibbonPlannerEntry JOIN gibbonCourseClass ON (gibbonPlannerEntry.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) JOIN gibbonCourseClassPerson ON (gibbonCourseClass.gibbonCourseClassID=gibbonCourseClassPerson.gibbonCourseClassID) JOIN gibbonCourse ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID) WHERE gibbonCourseClassPerson.gibbonPersonID=:gibbonPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND gibbonPlannerEntry.gibbonPlannerEntryID=:gibbonPlannerEntryID) UNION (SELECT gibbonPlannerEntry.gibbonPlannerEntryID, gibbonCourseClass.gibbonCourseClassID, gibbonUnitID, gibbonHookID, gibbonPlannerEntry.gibbonCourseClassID, gibbonPlannerEntry.name, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, gibbonPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM gibbonPlannerEntry JOIN gibbonCourseClass ON (gibbonPlannerEntry.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) JOIN gibbonPlannerEntryGuest ON (gibbonPlannerEntryGuest.gibbonPlannerEntryID=gibbonPlannerEntry.gibbonPlannerEntryID) JOIN gibbonCourse ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID) WHERE date=:date AND gibbonPlannerEntryGuest.gibbonPersonID=:gibbonPersonID AND gibbonPlannerEntry.gibbonPlannerEntryID=:gibbonPlannerEntryID2) ORDER BY date, timeStart" ; 
				}
				else if ($highestAction=="Lesson Planner_viewEditAllClasses") {
					$data=array("gibbonPlannerEntryID"=>$gibbonPlannerEntryID); 
					$sql="SELECT gibbonPlannerEntry.gibbonPlannerEntryID, gibbonCourseClass.gibbonCourseClassID, gibbonUnitID, gibbonHookID, gibbonPlannerEntry.gibbonCourseClassID, gibbonPlannerEntry.name, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class, date, timeStart, timeEnd, summary, gibbonPlannerEntry.description, teachersNotes, homework, homeworkDueDateTime, homeworkDetails, viewableStudents, viewableParents, 'Teacher' AS role, homeworkSubmission, homeworkSubmissionDateOpen, homeworkSubmissionDrafts, homeworkSubmissionType FROM gibbonPlannerEntry JOIN gibbonCourseClass ON (gibbonPlannerEntry.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) JOIN gibbonCourse ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID) WHERE gibbonPlannerEntry.gibbonPlannerEntryID=:gibbonPlannerEntryID ORDER BY date, timeStart" ; 
				}
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			
			if ($result->rowCount()!=1) {
				print "<div class='warning'>" ;
					print "Lesson does not exist or you do not have access to it." ;
				print "</div>" ;
			}
			else {
				$row=$result->fetch() ;
				
				$extra="" ;
				if ($viewBy=="class") {
					$extra=$row["course"] . "." . $row["class"] ;
				}
				else {
					$extra=dateConvertBack($date) ;
				}
				
				$params="" ;
				if ($_GET["date"]!="") {
					$params=$params."&date=" . $_GET["date"] ;
				}
				if ($_GET["viewBy"]!="") {
					$params=$params."&viewBy=" . $_GET["viewBy"] ;
				}
				if ($_GET["gibbonCourseClassID"]!="") {
					$params=$params."&gibbonCourseClassID=" . $_GET["gibbonCourseClassID"] ;
				}
				$params=$params."&subView=$subView" ;
												
												
				print "<div class='trail'>" ;
				print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/planner.php$params'>Planner $extra</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/planner_view_full.php$params&gibbonPlannerEntryID=$gibbonPlannerEntryID'>View Lesson Plan</a> > </div><div class='trailEnd'>Add Comment</div>" ;
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
					else if ($updateReturn=="fail4") {
						$updateReturnMessage ="Update failed some values need to be unique but were not." ;	
					}
					if ($updateReturn=="fail5") {
						$updateReturnMessage ="Update failed because you do not have access to this lesson for crowd assessment." ;	
					}
					else if ($updateReturn=="success0") {
						$updateReturnMessage ="Update was successful." ;	
						$class="success" ;
					}
					print "<div class='$class'>" ;
						print $updateReturnMessage;
					print "</div>" ;
				} 
		
				if ($_GET["submission"]!="true" AND $_GET["submission"]!="false") {
					print "<div class='warning'>" ;
						print "Your request is malformed." ;
					print "</div>" ;
				}
				else {
					if ($_GET["submission"]=="true") {
						$submission=true ;
						$gibbonPlannerEntryHomeworkID=$_GET["gibbonPlannerEntryHomeworkID"] ;
					}
					else {
						$submission=false ;
						$gibbonPersonID=$_GET["gibbonPersonID"] ;
					}
					
					if (($submission==true AND $gibbonPlannerEntryHomeworkID=="") OR ($submission==false AND $gibbonPersonID=="")) {
						print "<div class='warning'>" ;
							print "Your request is malformed." ;
						print "</div>" ;
					}
					else {
						if ($submission==true) {
							print "<h2>" ;
							print "Update Submission" ;
							print "</h2>" ;
							
							try {
								$dataSubmission=array("gibbonPlannerEntryHomeworkID"=>$gibbonPlannerEntryHomeworkID); 
								$sqlSubmission="SELECT gibbonPlannerEntryHomework.*, surname, preferredName FROM gibbonPlannerEntryHomework JOIN gibbonPerson ON (gibbonPlannerEntryHomework.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonPlannerEntryHomeworkID=:gibbonPlannerEntryHomeworkID" ;
								$resultSubmission=$connection2->prepare($sqlSubmission);
								$resultSubmission->execute($dataSubmission);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							
							if ($resultSubmission->rowCount()!=1) {
								print "<div class='warning'>" ;
									print "The specified submission could not be found." ;
								print "</div>" ;
							}
							else {
								$rowSubmission=$resultSubmission->fetch()
								?>
								<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/planner_view_full_submit_editProcess.php" ?>">
									<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
										<tr>
											<td> 
												<b>Student *</b><br/>
												<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
											</td>
											<td class="right">
												<input readonly name="courseName" id="courseName" maxlength=20 value="<? print formatName("", htmlPrep($rowSubmission["preferredName"]), htmlPrep($rowSubmission["surname"]), "Student") ?>" type="text" style="width: 300px">
											</td>
										</tr>
										<tr>
											<td> 
												<b>Status *</b><br/>
											</td>
											<td class="right">
												<select style="width: 302px" name="status">
													<option <? if ($rowSubmission["status"]=="On Time") { print "selected ";} ?>value="On Time">On Time</option>
													<option <? if ($rowSubmission["status"]=="Late") { print "selected ";} ?>value="Late">Late</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="right" colspan=2>
												<?
												print "<input type='hidden' name='search' value='" . $_GET["search"] . "'>" ;
												print "<input type='hidden' name='params' value='$params'>" ;
												print "<input type='hidden' name='gibbonPlannerEntryID' value='$gibbonPlannerEntryID'>" ;
												print "<input type='hidden' name='submission' value='true'>" ;
												print "<input type='hidden' name='gibbonPlannerEntryHomeworkID' value='$gibbonPlannerEntryHomeworkID'>" ;
												print "<input type='hidden' name='address' value='" . $_SESSION[$guid]["address"] . "'>" ;
												?>
												
												<input type="submit" value="Submit">
											</td>
										</tr>
									</table>
								</form>
							<?
							}
						}
						else {
							print "<h2>" ;
							print "Add Submission" ;
							print "</h2>" ;
							
							try {
								$dataSubmission=array("gibbonPersonID"=>$gibbonPersonID); 
								$sqlSubmission="SELECT surname, preferredName FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID" ;
								$resultSubmission=$connection2->prepare($sqlSubmission);
								$resultSubmission->execute($dataSubmission);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							
							if ($resultSubmission->rowCount()!=1) {
								print "<div class='warning'>" ;
									print "The specified student could not be found." ;
								print "</div>" ;
							}
							else {
								$rowSubmission=$resultSubmission->fetch()
							
								?>
								<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/planner_view_full_submit_editProcess.php" ?>" enctype="multipart/form-data">
									<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
										<tr>
											<td> 
												<b>Student *</b><br/>
												<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
											</td>
											<td class="right">
												<input readonly name="courseName" id="courseName" maxlength=20 value="<? print formatName("", htmlPrep($rowSubmission["preferredName"]), htmlPrep($rowSubmission["surname"]), "Student") ?>" type="text" style="width: 300px">
											</td>
										</tr>
										<tr>
											<td> 
												<b>Type *</b><br/>
											</td>
											<td class="right">
												<?
												if ($row["homeworkSubmissionType"]=="Link") {
													?>
													<input checked type="radio" id="type" name="type" class="type" value="Link" /> Link
													<input type="radio" id="type" name="type" class="type" value="None" /> None
													<?
												}
												else if ($row["homeworkSubmissionType"]=="File") {
													?>
													<input checked type="radio" id="type" name="type" class="type" value="File" /> File
													<input type="radio" id="type" name="type" class="type" value="None" /> None
													<?
												}
												else {
													?>
													<input type="radio" id="type" name="type" class="type" value="Link" /> Link
													<input type="radio" id="type" name="type" class="type" value="File" /> File
													<input checked type="radio" id="type" name="type" class="type" value="None" /> None
													<?
												}
												?>
											</td>
										</tr>
										<tr>
											<td> 
												<b>Version *</b><br/>
											</td>
											<td class="right">
												<?
												print "<select style='float: none; width: 302px' name='version'>" ;
													if ($row["homeworkSubmissionDrafts"]>0 AND $status!="Late" AND $resultVersion->rowCount()<$row["homeworkSubmissionDrafts"]) {
														print "<option value='Draft'>Draft</option>" ;
													}
													print "<option value='Final'>Final</option>" ;
												print "</select>" ;
												?>
											</td>
										</tr>
									
										<script type="text/javascript">
											/* Subbmission type control */
											$(document).ready(function(){
												<?
												if ($row["homeworkSubmissionType"]=="Link") {
													?>
													$("#fileRow").css("display","none");
													<?
												}
												else if ($row["homeworkSubmissionType"]=="File") {
													?>
													$("#linkRow").css("display","none");
													<?
												}
												else {
													?>
													$("#fileRow").css("display","none");
													$("#linkRow").css("display","none");
													<?
												}
												?>
											
												$(".type").click(function(){
													if ($('input[name=type]:checked').val() == "Link" ) {
														$("#fileRow").css("display","none");
														$("#linkRow").slideDown("fast", $("#linkRow").css("display","table-row")); 
													} else if ($('input[name=type]:checked').val() == "File" ) {
														$("#linkRow").css("display","none");
														$("#fileRow").slideDown("fast", $("#fileRow").css("display","table-row")); 
													} else {
														$("#fileRow").css("display","none");
														$("#linkRow").css("display","none");
													}
												 });
											});
										</script>
									
										<tr id="fileRow">
											<td> 
												<b>Submit File *</b><br/>
											</td>
											<td class="right">
												<input type="file" name="file" id="file"><br/><br/>
												<?
												print getMaxUpload() ;
											
												//Get list of acceptable file extensions
												try {
													$dataExt=array(); 
													$sqlExt="SELECT * FROM gibbonFileExtension" ;
													$resultExt=$connection2->prepare($sqlExt);
													$resultExt->execute($dataExt);
												}
												catch(PDOException $e) { }
												$ext="" ;
												while ($rowExt=$resultExt->fetch()) {
													$ext=$ext . "'." . $rowExt["extension"] . "'," ;
												}
												?>
											
												<script type="text/javascript">
													var file=new LiveValidation('file');
													file.add( Validate.Inclusion, { within: [<? print $ext ;?>], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false } );
												</script>
											</td>
										</tr>
										<tr id="linkRow">
											<td> 
												<b>Submit Link *</b><br/>
											</td>
											<td class="right">
												<input name="link" id="link" maxlength=255 value="" type="text" style="width: 300px">
												<script type="text/javascript">
													var link=new LiveValidation('link');
													link.add( Validate.Inclusion, { within: ['http://', 'https://'], failureMessage: "Address must start with http:// or https://", partialMatch: true } );
												</script>
											
											
											</td>
										</tr>
										<tr>
											<td> 
												<b>Status *</b><br/>
											</td>
											<td class="right">
												<select style="width: 302px" name="status">
													<option value="On Time">On Time</option>
													<option value="Late">Late</option>
													<option value="Exemption">Exemption</option>
												</select>
											</td>
										</tr>
									
										<tr>
											<td class="right" colspan=2>
												<?
												$params="" ;
												if ($_GET["date"]!="") {
													$params=$params."&date=" . $_GET["date"] ;
												}
												if ($_GET["viewBy"]!="") {
													$params=$params."&viewBy=" . $_GET["viewBy"] ;
												}
												if ($_GET["gibbonCourseClassID"]!="") {
													$params=$params."&gibbonCourseClassID=" . $_GET["gibbonCourseClassID"] ;
												}
												$params=$params."&subView=$subView" ;
											
												$count=0;
												try {
													$dataVersion=array("gibbonPersonID"=>$gibbonPersonID, "gibbonPlannerEntryID"=>$gibbonPlannerEntryID); 
													$sqlVersion="SELECT * FROM gibbonPlannerEntryHomework WHERE gibbonPersonID=:gibbonPersonID AND gibbonPlannerEntryID=:gibbonPlannerEntryID" ;
													$resultVersion=$connection2->prepare($sqlVersion);
													$resultVersion->execute($dataVersion);
												}
												catch(PDOException $e) { 
													print "<div class='error'>" . $e->getMessage() . "</div>" ; 
												}
											
												if ($resultVersion->rowCount()<1) {
													$count=$resultVersion->rowCount() ;
												}
											
												print "<input type='hidden' name='count' value='$count'>" ;
												print "<input type='hidden' name='lesson' value='" . $row["name"] . "'>" ;
												print "<input type='hidden' name='search' value='" . $_GET["search"] . "'>" ;
												print "<input type='hidden' name='params' value='$params'>" ;
												print "<input type='hidden' name='gibbonPlannerEntryID' value='$gibbonPlannerEntryID'>" ;
												print "<input type='hidden' name='submission' value='false'>" ;
												print "<input type='hidden' name='gibbonPersonID' value='$gibbonPersonID'>" ;
												print "<input type='hidden' name='address' value='" . $_SESSION[$guid]["address"] . "'>" ;
												?>
											
												<input type="submit" value="Submit">
											</td>
										</tr>
									</table>
								</form>
								<?
							}
						}
					}
				}
			}
		}
	}
}
?>