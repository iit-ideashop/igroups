<?php
//Uses a separate DB connection at the moment but redundant once ported to mysqli - Nash

require_once('../globals.php');
require_once('checkadmin.php');
$step = '';
$step = @$_GET['step'];
session_start();

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//iGroups updater script
//The purpose of this script is to update iGroups based on a cognos report
//The cognos report has to be uploaded without any changes
//The script will detect the current semester, match ipros from the spreadsheet to the igroups database
//create new users and associate them with the correct ipros.
//The script will initally run in a simulation phase which will process all of the data and if you are happy
//with the simulation you will be given the option of running the script in production mode. 
//USE AT YOUR OWN RISK
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//A few functions used by the whole script
function sterilizeInt($int){
	$input = intval($int);
	return $input;
}

function sterilizeStr($str){
	//Clean the input and set it as the output.
	$output = htmlspecialchars($str);
	return $output;
}
//Script setup
require_once '../classes/PHPExcel/IOFactory.php';
$newFileLocation = $disk_prefix . 'ods_uploads/classListFile.xlsx';
if($step != ''){
    @$database = new mysqli('localhost',$db_user,$db_pass,$db_name);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Let's start with step 1. Configuration
//Show the configuration page
//upload the file
//run the configuration script on submit
if($step == ''){
    //We are on step 0, show the configuration page
    $page = 'The Following script will update igroups with the provided registrar file. The registrar file this script uses can be found
        under SharedReports -> ODS -> Active Registration -> Registrar\'s Office -> "Class List with Student Program" -> Make sure you run a 2007 Excel Report. <br>
        <br>
        <form action="'.$_SERVER['PHP_SELF'].'?step=1" method="POST" enctype="multipart/form-data">
            Please upload the file here:<br>
            <input type="file" name="classListFile"><br>
            <br>
            <input type="submit" name="autoconfig" value="Upload File"><br>
            If you plan on performing the update in offline mode the following tables are used:<br>
            <ul>
            <li>People</li>
            <li>PeopleProjectMap</li>
            <li>Semesters</li>
            <li>Projects</li>
            <li>ProjectSemesterMap</li>
            <li>GroupAccessMap</li>
            </ul>
        </form>';
}elseif($step == 1){
    //in Step 1 the user has entered all of the configuration information on the previous page.
    //Here we have to query the database and find out the semester, link ipros in the spreadsheet to ipros in the database
    //
    $classlistfile = '';
    if (!empty($_FILES["classListFile"])) {
        $classlistfile = $_FILES["classListFile"];
    }else{
        echo 'File is empty..';
        exit;
    }
    //Let's make sure an xlsx has been uploaded
    if(!strpos($classlistfile['name'],'xlsx')){
        echo '<h1>You are literally not trying.. At least upload an excel file!!!</h1>';
        exit;
    }
    
    $page = '<h3>Running autoconfiguration</h3><br>
            File errors: '.$classlistfile['error'].'<br>
            File Location: '.$classlistfile['tmp_name'].'<br>';
    //Next step is to move the uploaded .xlsx file
    if(!@$database->ping()){
        echo '<h1>Database Connection failed.. I\'m surprised iGroups works at the moment!</h1>';
        exit;
    }
    
    if(move_uploaded_file($classlistfile['tmp_name'], $newFileLocation)){
        $page .='File moved successfully!<br>
                New File Location:'.$newFileLocation.'<br>';
    }

    //Let's read the file and find out what we can read!
    //The XLSX STRUCTURE IS AS FOLLOWS:
    //Column A - Academic period // trash...
    //Column B - CRN.... Trash for now
    //Column C - Subject. We should probably make sure it is IPRO
    //Column D - Course Number! 497 or 397 or maybe something new in the future?
    //Column E - Course Section - so the ipro number. 
    //Column F - Campus - Trash for now...
    //Column G - Registration Status. We have to make sure its RW(Find out what this means, if its WL then its Waitlisted)
    //Column H - Student's ID number. Not useful for now.
    //Column I - Student name format (lastname, first name)
    //Column J - Student's major - Trash but we have some duplicates thanks to dual majors
    //Column K - Student's email address
    //Let's read from the XLSX and have some fun.
    /** PHPExcel_IOFactory */
        
    $excelReader = PHPExcel_IOFactory::createReader('Excel2007');
    $readerObject = $excelReader->load($newFileLocation);
    //Quick check to make sure we have the right file
   
    if($readerObject->getActiveSheet()->getCell('A1')->getValue() != 'Class List by Campus'){
        echo '<h1>What is this crap you are feeding me!(You probably uploaded the wrong file.... make sure to upload the "Class List by Campus" Cognos Report)</h1>';
        exit;
    }
    //We are going to loop through the entire spreadsheet and find all of the ipros on the sheet
    //Lets find out how many rows are in this excel sheet
    $startRow = 1;
    $endrow = count($readerObject->getActiveSheet()->toArray());
    $page .= 'Row Count: '.$endrow .'<br>';
    //Iproarray is where we will store all of the ipro data. This is where we will match ipro to iproid in the database
    //Format is a standard associative array so $iproarray['ipronumber'] = iproid in the database for now it will be 0.
    $iproarray = array();
    $page .= '<h3>Reading IPRO\'s from Spreadsheet file </h3><br>';
    for($i = $startRow; $i <= $endrow; $i++){
        if($readerObject->getActiveSheet()->getCell('C'.$i)->getValue() == 'IPRO'){
            //We have a new IPRO, lets add it to the array
            $iproarray[$readerObject->getActiveSheet()->getCell('E'.$i)->getValue()] = 0;
            
        }
    }
    //Next let's loop through the array and display what we found
    foreach ($iproarray as $key=>$value){
        //$key is the ipro number from the spreadsheet
        //$value is now 0, we will perform a lookup next
        $page .= 'Found new IPRO '.$key.'<br>';
    }
    //Next we have to find the current semester from the iprodatabase
    $sql = "SELECT * FROM Semesters WHERE bActiveFlag='1' LIMIT 1";
    $query = $database->query($sql);
    $semester = $query->fetch_assoc();
    $page .= 'The Current Semester ID is '.$semester['iID']. ' '. $semester['sSemester'].'<br>';
    //Now that we know the semester ID we have to pull the current ipros from this semester
    
    $ipro_id_array = array();
    $ipro_name_array = array();
    $sql = "SELECT * FROM ProjectSemesterMap WHERE iSemesterID='".$semester['iID']."'";
    $query = $database->query($sql);
    while($rows = $query->fetch_assoc()){
        $sql = "SELECT * FROM Projects WHERE iID='".$rows['iProjectID']."' LIMIT 1";
        $iproresult = $database->query($sql);
        $projectListing = $iproresult->fetch_assoc();
        $ipro_id_array[$rows['iProjectID']] = $projectListing['sIITID'];
        $ipro_name_array[$rows['iProjectID']] = $projectListing['sName'];
    }
    $page .= '<h3>Searching database for Active IPRO Records</h3>';
    foreach($ipro_id_array as $key=>$value){
        //$Key is the project id
        //$value is the project's IITID
        //$ipro_name_array[$key] will get the database name for it
        $page .= 'Located IPRO in database: '.$key.':'.$value.' - '.$ipro_name_array[$key].'<br>';
    }
    $page .= '<h3>Attempting to find a needle in a haystack</h3>';
    //Next we have to map the spreadsheet values to id's
    //We will also include an array of how many times each project id is used to attempt to find duplicates.
    $projectIDAssociationCountArray = array();
    foreach($iproarray as $ipronumber=>$projectid){
        //$key is the ipro number found on the spreadsheet
        //$value is the project id in the database
        foreach($ipro_id_array as $dbprojectid=>$iitid){
            
            if(strpos($iitid, "".$ipronumber)){
                $iproarray[$ipronumber] = $dbprojectid;
                $page .='Found a needle in a Haystack:'. $iitid . 'Needle:' . $ipronumber. '<br>';
                if(array_key_exists($dbprojectid, $projectIDAssociationCountArray)){
                    //Key exists
                    $projectIDAssociationCountArray[$dbprojectid] = $projectIDAssociationCountArray[$dbprojectid] + 1;
                }else{
                    $projectIDAssociationCountArray[$dbprojectid] = 1;
                }
            }
        }
    }
    //Let see if we got this right
    $page .= '<h3>Autoconfiguration Complete!! Your input is required!</h3>';
    $page .= 'This section contains the IPRO section number, the predicted project id, and the project name. Please make sure that the project id\'s match the project.<br>
            The system attempts to find duplicates and point them out to you. You should still double check that the project ID\'s match the IPRO Projects';
    //In the form field we are going to generate a special session key to make sure that when we go to step 2 we are not being bullshitted..
    $guid = uniqid();
    $_SESSION['uniqueid'] = $guid;
    $page .= '<form action="?step=2&sessionKey='.$guid.'&runType=Validate" method="POST">';
    $page .= '<table> <tr><th>IPRO number</th><th>Project ID</th><th>Project Name</th></tr>';
    foreach($iproarray as $ipronumber=>$projectid){
        $ipronumberTrailer = '';
        if($projectIDAssociationCountArray[$projectid] >1){
            $ipronumberTrailer = '***';
        }
        $page .= '<tr>
                <td>'.$ipronumberTrailer.' '.$ipronumber.' '.$ipronumberTrailer.'</td>
                <td><input type="text" name="mapping-'.$ipronumber.'" value="'.$projectid.'"></td>
                <td>('.$ipro_id_array[$projectid].')'.$ipro_name_array[$projectid].'</td>
                </tr>';
        
        
    }
    $page .= '</table>';
    //Here we will serialize some of the arrays we have to speed things up
    $page .= '<input type="hidden" name="ser_iproarray" value=\''.serialize($iproarray).'\'>';
    $page .= '<input type="hidden" name="currentSemester" value="'.$semester['iID'].'">';
    $page .= '<u>A little thing to keep in mind</u><br>';
    $page .= 'This script reads all of the records on the provided spreadsheet one by one. <br>'
            . 'Then it proceeds to <b>validate</b> that the record has an iGroups account and <b>verifies<br>'
            . 'registration</b> in the correct ipro. After it has finished doing all of your <b>dirty<br>'
            . 'work</b> for you the script proceeds to <b>clean up the database</b> and drop users who dropped<br>'
            . 'the IPRO class.(They didn\'t pay for the class anyays so no hard feelings right?). <br>'
            . '<b>Main point</b>: This script may take a <b>minute or two</b> to run. As long as you got the spinning spinner <br>'
            . 'thingy and you see stuff is happening <b><u>DON\'T</u></b> get <b><u>ANTSY IN THE PANTSIE</u></b> and reload the page. <br>'
            . 'The script is coded for <b>you people</b> and will make you <b>start all over</b>. <i>So there</i>.<br>';
    $page .= 'Without further Adiue. Press the button below. <br>';
    $page .= '<input type="submit" value="Proceed with the Wizardry!"> ';
    $page .= '</form>';
    
}//End step1
elseif($step == 2){
    //in step 2 we run the code in a verify/readonly stage. This runs all of the commands in a test mode
    //and only tells you what it would like to do, ie Read Only. 
    //The next step will rerun step 2 in a write mode and can be used to apply all of the changes.
    //Let's start with testing to see if the file is still there..
    //Grab the run type
    $runType = $_GET['runType'];
    if($runType == 'Validate'){
        $page = '<h3>Running in Read-Only Mode!</h3>';
    }elseif($runType == "Production"){
        $page = '<h3>Running in Production Mode!</h3>';
    }else{
        echo 'We don\'t actually know what you are trying to do here... RunType is not set or is invalid..';
        exit;
    }
        if((!isset($_SESSION['uniqueid'])) || (!isset($_GET['sessionKey'])) || ($_GET['sessionKey'] =='')){
        echo 'It seems that you are missing a session key... That\'s Fine!! <a href="'.$_SERVER['PHP_SELF'].'">Start Over to get a Session Key</a> ';
        exit;
    }
    if($_SESSION['uniqueid'] == @$_GET['sessionKey']){
        $_SESSION['uniqueid'] = '';
    }else{
        echo 'It seems that your session key does not match... Fishy.. Maybe ----> <a href="'.$_SERVER['PHP_SELF'].'">Start Over to get a Session Key</a> ';
        exit;
    }
    
    if(file_exists($newFileLocation)){
        $page .= 'Phew... The File still exists!';
    }else{
        echo 'OH NOES!!! THE XLSX FILE IS GONE!!!!!';
        exit;
    }
    
    //Let's grab the data from the last step via the serialized hidden fields!!! :D
    $page .= '<h3>Rediscovering the IPROs</h3>';
    $iproarray = unserialize($_POST['ser_iproarray']);
    foreach($iproarray as $key=>$value){
        $page .= 'Discovered IPRO '.$key.'<br>';
    }
    $page .= '<h3>Mapping the IPROs</h3>';
    foreach($iproarray as $key=>$value){
        $iproarray[$key] = $_POST['mapping-'.$key];
        $page .= 'IPRO '.$key.' : '.$iproarray[$key].'<br>';
    }
    if($runType == 'Validate'){
        $page .= '<h3>Here is what we would like to do..</h3>';
    }elseif ($runType == 'Production') {
        $page .= '<h3>Applying the following to the database</h3>';
    }

    //Now that we know which file belongs where it's time to do the dirty work of looking for student ipro associations
    //Let's start with the logical excel file to database comparison
    //So we run through the excel file and find any new registrants
    /** PHPExcel_IOFactory */
    $excelReader = PHPExcel_IOFactory::createReader('Excel2007');
    $readerObject = $excelReader->load($newFileLocation);
    $startRow = 1;
    $endrow = count($readerObject->getActiveSheet()->toArray());
    $page .= 'Row Count: '.$endrow .'<br>';
    $currentSemester = $_POST['currentSemester'];
    //Here we will do all of the checking in a single function
    //We have to read a single row at a time
    //We will read section E to find the 'IPRO number' then take that number and store it if it has changed and then parse the emails associated with the IPRO
    //It is much easier than it sounds. check out the code below
    $currentSection = '';
    $actionCounter = 0;
    for($i = $startRow; $i <= $endrow; $i++){
        if($readerObject->getActiveSheet()->getCell('E'.$i)->getValue() != ''){
                //change the section we are using
                echo 'Changing Section! from'.$currentSection;
                $currentSection = $readerObject->getActiveSheet()->getCell('E'.$i)->getValue();
                echo 'To '.$currentSection.'<br>';
        }
        if((strpos($readerObject->getActiveSheet()->getCell('K'.$i)->getValue(),'@')) 
                && ($readerObject->getActiveSheet()->getCell('K'.$i)->getValue() != $readerObject->getActiveSheet()->getCell('K'.($i-1))->getValue()) 
                && ($readerObject->getActiveSheet()->getCell('G'.$i)->getValue() != 'WL')){
            //HOT DOG WE HAVE AN EMAIL ADDRESS!!! PARSE THIS ROW!
            
            //Let's see if the user is already in the Person database by checking to see if the email address has an account
            $sql = "SELECT * FROM People WHERE sEmail='".$readerObject->getActiveSheet()->getCell('K'.$i)->getValue()."' LIMIT 1";
            $query = $database->query($sql);
            if($query->num_rows == 0){
                //Create an account for the user in iGroups
                
                if($runType == 'Validate'){
                    $page .= 'Create an iGroups account for '.$readerObject->getActiveSheet()->getCell('K'.$i)->getValue().'<br>';
                    $page .= 'Register '.$readerObject->getActiveSheet()->getCell('K'.$i)->getValue().' For IPRO '.$currentSection. '<br>';
                    $actionCounter += 2;
                }elseif($runType == 'Production'){
                    //We are running in production mode.
                    //We have to create an account then register the person in the ipro
                    $userEmail = $readerObject->getActiveSheet()->getCell('K'.$i)->getValue();
                    $nameArray = explode(',',$readerObject->getActiveSheet()->getCell('I'.$i)->getValue());
                    $firstName =  $nameArray[1];
                    $lastName = $nameArray[0];
                    
                    $emailArray = explode('@',$readerObject->getActiveSheet()->getCell('K'.$i)->getValue());
                    $userPassword = md5($emailArray[0]);
                    $sql = "INSERT INTO People(sFName,sLName,sEmail,sPassword,iUserTypeID,bActiveFlag,bReceiveNotifications) "
                            . " VALUES('".$firstName."','".$lastName."','".$userEmail."','".$userPassword."',4,1,1)";
                    $query = $database->query($sql);
                    $page .= 'Created iGroups account for '.$userEmail.'<br>';
                    //Now to register this user for the required ipro
                    $sql = "SELECT iID FROM People WHERE sEmail='".$userEmail."'";
                    $query = $database->query($sql);
                    $queryData = $query->fetch_assoc();
                    $userID = $queryData['iID'];
                    //Register the user for the required IPRO
                    $sql = "INSERT INTO PeopleProjectMap(iPersonID,iProjectID,iSemesterID,iPerProjRelationTypeID) "
                            . "VALUES('".$userID."','".$iproarray[$currentSection]."','".$currentSemester."',4)";
                    $query = $database->query($sql);
                    $page .= 'Registered '.$userEmail.' For IPRO '.$currentSection.':'.$iproarray[$currentSection].'<br>';
                }
                }else{
                //User has an account
                    
                $person = $query->fetch_assoc();
                $sql = "SELECT * FROM PeopleProjectMap WHERE iPersonID='".$person['iID']."' AND iProjectID='".$iproarray[$currentSection]."' AND iSemesterID='".$currentSemester."' LIMIT 1";
                $query = $database->query($sql);
                if($query->num_rows == 0){
                    if($runType == 'Validate'){
                        $page .= 'Register '.$readerObject->getActiveSheet()->getCell('K'.$i)->getValue().' For IPRO '.$currentSection.'<br>';
                        $actionCounter += 1;
                    }elseif($runType == 'Production'){
                        //Run the insert into the database
                        $sql = "INSERT INTO PeopleProjectMap(iPersonID,iProjectID,iSemesterID,iPerProjRelationTypeID) "
                                . "VALUES('".$person['iID']."','".$iproarray[$currentSection]."','".$currentSemester."',4)";
                        $query = $database->query($sql);
                        $page .= 'Registered '.$person['sEmail'].' for IPRO '.$currentSection.':'.$iproarray[$currentSection].'<br>';
                    }
                }
            }
        }
    }
    if($actionCounter == 0){
        $page .='<img src="images/nothingHere.jpg">';
        //If there is nothing to do, let's proceed to step 3?
        $runType = 'Production';
    }else{
        $page .='Action Count:'.$actionCounter.'<br>';
    }
    //Next we have to generate a session key and allow for step 2 and 3 to be run in production mode.
    $sessionKey = uniqid();
    $_SESSION['uniqueid'] = $sessionKey;
    if($runType == 'Production'){
        //We want to generate a form for step 3 which will clean up the database
        $page .= '<h3>Proceed to cleanup the database and mappings</h3><br>
            <form action="?step=3&sessionKey='.$sessionKey.'&runType=Validate" method="POST">';
    }else{
        //We have to generate a form to run step 2 in production
    $page .= '<h3>Do you want to continue with this wizardry??</h3><br>
            <form action="?step=2&sessionKey='.$sessionKey.'&runType=Production" method="POST">';
    }
    //We have to transfer over the mappings!!!
    foreach($iproarray as $key=>$value){
        $page .= '<input type="hidden" name="mapping-'.$key.'" value="'.$_POST['mapping-'.$key].'">';
    }
    $page .= '<input type="hidden" name="ser_iproarray" value=\''.serialize($iproarray).'\'>';
    $page .= '<input type="hidden" name="currentSemester" value="'.$_POST['currentSemester'].'">';
    if($runType == 'Production'){
        //Button should say step 3 clean up phase
        $page .= '<input type="submit" value="Proceed to cleanup phase">
             </form>';
        
    }else{
        //Show a step 2 production button
        $page .= '<input type="submit" value="Run update in Production mode">
             </form>';
    }
}elseif($step == '3'){
    //This is the final step in which we clean up the database, this will run in the same way as step 2
    //A verification step and a Production step.
    //We should probably pull the mappings
    $runType = $_GET['runType'];
    if($runType == 'Validate'){
        $page = '<h3>Running in Read-Only Mode!</h3>';
    }elseif($runType == "Production"){
        $page = '<h3>Running in Production Mode!</h3>';
    }else{
        echo 'We don\'t actually know what you are trying to do here... RunType is not set or is invalid..';
        exit;
    }
        if((!isset($_SESSION['uniqueid'])) || (!isset($_GET['sessionKey'])) || ($_GET['sessionKey'] =='')){
        echo 'It seems that you are missing a session key... That\'s Fine!! <a href="'.$_SERVER['PHP_SELF'].'">Start Over to get a Session Key</a> ';
        exit;
    }
    if($_SESSION['uniqueid'] == @$_GET['sessionKey']){
        $_SESSION['uniqueid'] = '';
    }else{
        echo 'It seems that your session key does not match... Fishy.. Maybe ----> <a href="'.$_SERVER['PHP_SELF'].'">Start Over to get a Session Key</a> ';
        exit;
    }
    
    if(file_exists($newFileLocation)){
        $page .= 'Phew... The File still exists!';
    }else{
        echo 'OH NOES!!! THE XLSX FILE IS GONE!!!!!';
        exit;
    }
    
    //Let's grab the data from the last step via the serialized hidden fields!!! :D
    $page .= '<h3>Rediscovering the IPROs</h3>';
    $iproarray = unserialize($_POST['ser_iproarray']);
    foreach($iproarray as $key=>$value){
        $page .= 'Discovered IPRO '.$key.'<br>';
    }
    $page .= '<h3>Mapping the IPROs</h3>';
    foreach($iproarray as $key=>$value){
        $iproarray[$key] = $_POST['mapping-'.$key];
        $page .= 'IPRO '.$key.' : '.$iproarray[$key].'<br>';
    }
    if($runType == 'Validate'){
        $page .= '<h3>Here is what we would like to do..</h3>';
    }elseif ($runType == 'Production') {
        $page .= '<h3>Applying the following to the database</h3>';
    }
    //Next we have to build a local database and run a comparison on the actual database and find out who dropped.
    //Let's start by building the local database in an array format
    //The format of the array is [UserID] = array(ipro sections registered)
    /** PHPExcel_IOFactory */
    $excelReader = PHPExcel_IOFactory::createReader('Excel2007');
    $readerObject = $excelReader->load($newFileLocation);
    $startRow = 1;
    $endrow = count($readerObject->getActiveSheet()->toArray());
    $page .= 'Row Count: '.$endrow .'<br>';
    $currentSemester = $_POST['currentSemester'];
    $actionCounter = 0;
    $currentSection = null;
    $spreadsheetDatabase = array();
    for($i = $startRow; $i <= $endrow; $i++){
        if($readerObject->getActiveSheet()->getCell('E'.$i)->getValue() != ''){
            //change the section we are using
            $currentSection = $readerObject->getActiveSheet()->getCell('E'.$i)->getValue();
        }
        //We have to make sure that the row we are parsing is an actual email and that it is not a duplicate line for dual majors
        if((strpos($readerObject->getActiveSheet()->getCell('K'.$i)->getValue(),'@')) 
                && ($readerObject->getActiveSheet()->getCell('K'.$i)->getValue() != $readerObject->getActiveSheet()->getCell('K'.($i-1))->getValue()) 
                && ($readerObject->getActiveSheet()->getCell('G'.$i)->getValue() != 'WL')){
            //Let's find the user id of this email
            $sql = "SELECT iID FROM People WHERE sEmail='".$readerObject->getActiveSheet()->getCell('K'.$i)->getValue()."' LIMIT 1";
            $query = $database->query($sql);
            $queryData = $query->fetch_assoc();
            $userID = $queryData['iID'];
            //Now that we know the user id we have to associate the userid with the ipro section id
            //We can use $iproarray to find the ipro and mappings
            
            if(array_key_exists($userID, $spreadsheetDatabase)){
                //We have a userid entry, we have to push to the array in the value field
                array_push($spreadsheetDatabase[$userID], $iproarray[$currentSection]);
            }else{
                //new userid, create an array with associated ipros
                $spreadsheetDatabase[$userID] = array($iproarray[$currentSection]);
            }
        }
    }
    $page .= '<h3>Building spreadsheet database</h3><br>';
    foreach($spreadsheetDatabase as $key=>$value){
        $page .= 'UserID:'.$key.' Value:'.serialize($value).'<br>';
    }
    //next we have to pull the database entries and put them into an array
    $databaseArray = array();
    $sql = "SELECT * FROM PeopleProjectMap WHERE iSemesterID='".$currentSemester."'";
    $query = $database->query($sql);
    while($queryData = $query->fetch_assoc()){
        //We only want to build the database of users in igroups and leave all admins and guests alone
        $accessSQL = "SELECT * FROM GroupAccessMap WHERE iPersonID='".$queryData['iPersonID']."' AND iSemesterID='".$currentSemester."' AND iGroupID='".$queryData['iProjectID']."'";
        $accessQuery = $database->query($accessSQL);
        if($accessQuery->num_rows == 0){
            //We found out the user is a simple user, not an admin, guest or moderator.
            if(array_key_exists($queryData['iPersonID'], $databaseArray)){
                //The user is in multiple ipros. let's append this one to the already created list
                array_push($databaseArray[$queryData['iPersonID']], $queryData['iProjectID']);
            }else{
                //The array key doesent exist, let's create an array..
                $databaseArray[$queryData['iPersonID']] = array($queryData['iProjectID']);
            }
        }
    }
    $page .= '<h3>Local database replication completed. Running dataset comparison now...</h3><br>';
    //Now we have to compare the local database and propose what we want to do. We will be using the spreadsheet database as the running database
    //and removing data from the local database as we move through
    foreach ($spreadsheetDatabase as $key=>$value){
        //$key is the user id
        //$value is the array containing the user's ipros
        //We should loop through the user's ipro list
        
        foreach($value as $iproSection){
            //Here we have to run a comparison and remove these values from the databaseArray
            $removalKey = array_search($iproSection, $databaseArray[$key]);
            if((!$removalKey) && ($removalKey != 0)){
                echo 'It seems that the spreadsheet has not yet been loaded into the database. Please do that first..';
                echo $removalKey;
                exit;
            }
            if(count($databaseArray[$key]) == 1){
                //Remove the userid record. We are done with it
                unset($databaseArray[$key]);
            }else{
                //The user has another record, so let's just remove this one record
                unset($databaseArray[$key][$removalKey]);
            }
        }
    }
    $page .= 'Comparison complete!!<br>';
    $page .= 'Here is the results of the dataset duplication checking<br>'; 
    foreach($databaseArray as $key=>$value){
        //$key is the userid
        //$value is the ipros the user has dropped.
        $sql = "SELECT sEmail FROM People WHERE iID='".$key."' LIMIT 1";
        $query = $database->query($sql);
        $queryData = $query->fetch_assoc();
        foreach($value as $droppedIPRO){
            if($runType == 'Validate'){
                $page .= 'Drop '.$queryData['sEmail'].' From ProjectID:'.$droppedIPRO.'<br>';
            }elseif($runType == 'Production'){
                //Here we actuall delete the data.
                $sql = "DELETE FROM PeopleProjectMap WHERE iPersonID='".$key."' AND iProjectID='".$droppedIPRO."' AND iSemesterID='".$currentSemester."' LIMIT 1";
                $query = $database->query($sql);
                $page .= 'Dropped '.$queryData['sEmail'].' From ProjectID:'.$droppedIPRO.'<br>';
            }
        }
    }
    if($runType == 'Validate'){
        //Show a form to run the cleanup phase
        $sessionKey = uniqid();
        $_SESSION['uniqueid'] = $sessionKey;
        $page .='<form action="?step=3&runType=Production&sessionKey='.$sessionKey.'" method="post">';
        foreach($iproarray as $key=>$value){
            $page .= '<input type="hidden" name="mapping-'.$key.'" value="'.$_POST['mapping-'.$key].'">';
        }
        $page .= '<input type="hidden" name="ser_iproarray" value=\''.serialize($iproarray).'\'>';
        $page .= '<input type="hidden" name="currentSemester" value="'.$_POST['currentSemester'].'">';
        $page .= '<input type="submit" value="Apply to the database">';
    }elseif($runType== 'Production'){
        //Show a link to start all over
        $page .='<h3>This script has complete. No other actions are available</h3>'
                . '<a href="'.$_SERVER['PHP_SELF'].'">Start over from the begining!!!</a>';
    }
}



/*
echo $readerObject->getActiveSheet()->getCell('A2')->getValue();
echo $readerObject->getActiveSheet()->getCell('A3')->getValue();
echo $readerObject->getActiveSheet()->getCell('A4')->getValue();
echo $readerObject->getActiveSheet()->getCell('B4')->getValue();
echo $readerObject->getActiveSheet()->getCell('C4')->getValue();
echo $readerObject->getActiveSheet()->getCell('A5')->getValue();
echo $readerObject->getActiveSheet()->getCell('A6')->getValue();
echo $readerObject->getActiveSheet()->getCell('A7')->getValue();
echo $readerObject->getActiveSheet()->getCell('A8')->getValue();
*/


//------Start XHTML Output--------------------------------------//

require('doctype.php');
require('appearance.php');
echo "<link rel=\"stylesheet\" href=\"../skins/$skin/default.css\" type=\"text/css\" title=\"$skin\" />\n";
foreach($altskins as $altskin)
	echo "<link rel=\"alternate stylesheet\" href=\"../skins/$altskin/default.css\" type=\"text/css\" title=\"$altskin\" />\n";
?>

<title><?php echo $appname; ?> - Update from Cognos/ODS</title>
</head>
<body>
<?php
	
/**** begin html head *****/
   require('htmlhead.php'); //starts main container
  /****end html head content ****/	

        echo $page;

//include rest of html layout file
  require('htmlcontentfoot.php');// ends main container
?></body></html>
