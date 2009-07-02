<?php
/* This is a very "Different" kind of class.  Because the database has two fundamentally different ways of representing former and present data,
it was necessary to create a data structure that could represent two completely different sets of MYSQL data in identical means to php.

The result is a data item that carries a flag, and depending on what the status of the flag is, will determine how the data is retrieved from the DB

*/
include_once( "superstring.php" );
include_once( "person.php" );		
include_once( "/srv/igroups/21/classes/group.php" );
include_once( "semester.php" );
$path = dirname(__FILE__) . '/';
include_once( $path. "../nuggetTypes.php" );

if( !class_exists( "Nugget" ) ) {
	class Nugget{
		var $id, $name, $desc, $group, $pub, $date, $semester, $old, $published, $verified, $vby, $vtime;
		var $db;
		
		//the following is used to create a nugget object based on an existing database entity of the given id
		function Nugget( $id, $db, $old) {
			$this->id = $id;
			$this->db = $db;
			$this->old = $old;
			if($this->old != 1){
				if( $nugget = mysql_fetch_array( $db->igroupsQuery("SELECT * FROM iGroupsNuggets WHERE iNuggetID = $id" ))){
					$this->name = new SuperString( $nugget['sTitle'] );
					$this->desc = new SuperString( $nugget['sDescription'] );
					$this->pub = $nugget['bRestricted'];
					$this->date = $nugget['dCreated'];
					$this->group = $nugget['iGroupID'];
					$this->semester = $nugget['iSemesterID'];
					$this->published = $nugget['bStatus'];
					$this->verified = false;
					if($nugget['iVerified'])
					{
						$this->verified = true;
						$this->vby = new Person($nugget['iVerified'], $db);
						$this->vtime = $nugget['dVerified'];
					}
				}
			}
			else{
				if( $row = mysql_fetch_array($db->igroupsQuery("SELECT * FROM Nuggets WHERE iID = {$this->id}"))){
					$this->name = new SuperString( $row['sTitle']);
					$this->desc = new SuperString( $row['sAbstract']);
					$this->pub= $row['bRestricted'];
					$this->date = $row['dCreated'];
					if($row = mysql_fetch_array($db->igroupsQuery("SELECT * FROM ProjectNuggetMap WHERE iNuggetID = $this->id"))){
						$this->group = $row['iProjectID'];
						$this->semester = $row['iSemesterID'];
					}else{
						print "I Should not see this..failing id is ---->".$this->id."<-----";
					}
				}
			}
		}
		
		function getID(){
			return $this->id;
		}
		
		function isVerified() {
			return $this->verified;
		}
		
		function whoVerified() {
			return $this->verified ? $this->vby : false;
		}
		
		function whenVerified() {
			return $this->verified ? $this->vtime : false;
		}
		
		function verify($admin) {
			$g = new Group($this->group, 0, $this->semester, $this->db);
			if($admin->isGroupAdministrator($g))
				return $db->igroupsQuery("update iGroupsNuggets set iVerified={$admin->getID()}, dVerified=NOW() WHERE iNuggetID = {$this->id}");
			else
				return false;
		}
		
		function getAuthors(){
			$loop = 0;
			if( $this->old != 1){
				$results = $this->db->igroupsQuery("Select iAuthorID FROM nuggetAuthorMap where iNuggetID=$this->id");
				$Authors = array();
				while( $row = mysql_fetch_array( $results)){
					$Authors[$loop] = new Person($row['iAuthorID'], $this->db);
					$loop += 1;
				}
				return $Authors;
			}else{
				$results = $this->db->igroupsQuery("Select iPersonID FROM PeopleNuggetMap where iNuggetID=$this->id");
				$Authors = array();
				while( $row = mysql_fetch_array( $results)){
					$Authors[] = new Person($row[0], $this->db);

				}
				return $Authors;
			}
		}
		
		function isAuthor($authID){
			$authors = $this->getAuthorIDs();
			if (in_array($authID, $authors)){
				return true;
			}else{
				return false;
			}
		}
		
		function getStatus(){
			return $this->published;
		}
		
		function getAuthorIDs(){
			$loop = 0;
			if($this->old != 1){
				$results = $this->db->igroupsQuery("Select iAuthorID FROM nuggetAuthorMap where iNuggetID=$this->id");
				$Authors = array();
				while( $row = mysql_fetch_array( $results)){
					$Authors[$loop] = $row['iAuthorID'];
					$loop += 1;
				}
				return $Authors;
			}else{
				$results = $this->db->igroupsQuery("Select iPersonID FROM PeopleNuggetMap where iNuggetID=$this->id");
				$Authors = array();
				while( $row = mysql_fetch_array( $results)){
					$Authors[] = $row[0];
				}
				return $Authors;
			}
		}
		function makePrivate(){
			$this->pub = 1;
			$query = "UPDATE iGroupsNuggets SET bRestricted = 1 WHERE iNuggetID = {$this->id}";
			$this->db->igroupsQuery($query);
		}
		
		function makePublic(){
			$this->pub = 0;
			$query = "UPDATE iGroupsNuggets SET bRestricted=0 WHERE iNuggetID = {$this->id}";
			$this->db->igroupsQuery($query);
		}

		function publish(){
			$this->published = 1;
			$query = "UPDATE iGroupsNuggets SET bStatus = 1 WHERE iNuggetID = $this->id";
			$this->db->igroupsQuery($query);
		}
		function getFileIDs(){
			if($this->old !=1){
				$loop = 0;
				$results = $this->db->igroupsQuery("SELECT iFileID FROM nuggetFileMap WHERE iNuggetID=$this->id");
				while($row = mysql_fetch_array($results)){
					$Files[$loop] = $row['iFileID'];
					$loop += 1;
				}
				
				return $Files;
			}
			else{
				$results = $this->db->igroupsQuery("SELECT sDiskName, sOrigName FROM NuggetFiles WHERE iNuggetID = $this->id");
				$nuggetFiles = array();
				while($row = mysql_fetch_array($results)){
					$nuggetFiles[$row[0]] = $row[1];
				}
				return $nuggetFiles;
			}
		}
		
		function getFiles(){
			if($this->old !=1){
				$loop = 0;
				$results = $this->db->igroupsQuery("Select distinct iFileID FROM nuggetFileMap WHERE iNuggetID={$this->id}");
				$Files= array();
				while( $row = mysql_fetch_array($results)){
					$Files[$loop] = new File($row['iFileID'], $this->db);
					$loop +=1;
				}
				return $Files;
			}else{
				$results = $this->db->igroupsQuery("SELECT iID, sDiskName, sOrigName FROM NuggetFiles WHERE iNuggetID = {$this->id}");
				$nuggetFiles = array();
				while($row = mysql_fetch_array($results)){
					$nuggetFiles[] = array($row['iID'], $row['sOrigName']);
				}
				return $nuggetFiles;			 
			}
		}
		
		function isDefault(){
			global $_DEFAULTNUGGETS;
			if($this->old !=1){
				$tempName = $this->name->getString();
				if(in_array($tempName, $_DEFAULTNUGGETS)){
					return true;
				}else{
					return false;
				}
			}else{
				//N/A
			}
		}
		
		function isPrivate(){
			if($this->pub == 1){
				return true;
			}else{
				return false;
			}
		}
		function isPrimaryAuthor($authorID){
			if($this->old != 1){
				$results = $this->db->igroupsQuery("Select bPrimaryAuthor FROM nuggetAuthorMap WHERE iAuthorID= $authorID AND iNuggetID=$this->id" );
				if($row = mysql_fetch_array($results)){
					if($row['bPrimaryAuthor']== 1){
						return true;
					}
					else
						return false;
				}
			}else{
				$results = $this->db->igroupsQuery("Select iPerNugRelationTypeID FROM PeopleNuggetMap WHERE iPersonID= $authorID AND iNuggetID=$this->id" );
				if($row = mysql_fetch_array($results)){
					if($row['iPerNugRelationTypeID']== 1){
						return true;
					}
					else
						return false;
				}
			}
		}
		function getSemesterString(){
			return $this->semester;
		}
		function getSemester(){
			return new Semester($this->semester, $this->db);
		}
		
		function isThisSemester(){
			$tempSem = new Semester($this->semester, $this->db);
			if($tempSem->isActive()){
				return true;
			}
			else
				return false;
		}
		function getType(){
			if ($this->name == null)
				return "";
			else
				return $this->name->getString();
		}
		
		function setType($name){
			$this->name->setString($name);
		}
		
		function getDesc(){
			return $this->desc->getString();
		}
		
		function setDesc($desc){
			return $this->desc->setString($desc);
		}
		
		function isOld(){
			if($this->old==1){
				return true;
			}else{
				return false;
			}
		}
		
		function getOld(){
			if($this->old==1){
				return 1;
			}else{
				return 0;
			}
		}
		
		function getGroupID(){
			return $this->group;
		}
			
		function getDescShort(){
			return substr($this->desc->getString(), 0, 35);
		}
		
		function getDate(){
			$date = substr($this->date, 0, strpos($this->date, ' '));
			$temp = explode( "-", $date );
			return date( "m/d/Y", mktime( 0, 0, 0, $temp[1], $temp[2], $temp[0] ));
		}
		
		function getGroupName(){
			$query = "SELECT sIITID from Projects where iID = $this->group";
			$results = $this->db->igroupsQuery($query);
			if($row = mysql_fetch_array($results)){
				return $row[0];
			}
		}
		
		function removeAuthor($authorID){
			if($this->old != 1){
				$query = 'DELETE FROM nuggetAuthorMap WHERE iAuthorID = '.$authorID.' AND iNuggetID = '.$this->id;
				$this->db->igroupsQuery($query);
				return true;
			}else{
				//N/A
			}
		}
		
		function addAuthor($authorID){
			if($this->old != 1){
				$query = "INSERT INTO nuggetAuthorMap (iNuggetID, iAuthorID) VALUES('".$this->id."', '".$authorID."')";
				$this->db->igroupsQuery($query);
				return true;
			}else{
				//N/A
			}
		}

		function addFile($fileID){
			if($this->old != 1){
				$query = "INSERT INTO nuggetFileMap (iFileID, iNuggetID) VALUES($fileID, $this->id)";
				$this->db->igroupsQuery($query);
				return true;
			}
		}
		
		function removeFile($fileID){
			if($this->old != 1){
				$query = 'DELETE FROM nuggetFileMap WHERE iFileID = "'.$fileID.'" AND iNuggetID = "'.$this->id.'"';
				$this->db->igroupsQuery($query);
				return true;
			}else{
				//N/A
			}
		}
		
		function updateDB($primary){
			if($this->old != 1){
				$query= 'UPDATE iGroupsNuggets SET sTitle = "'.$this->name->getString().'", sDescription = "'.$this->desc->getString().	'" WHERE iNuggetID = "'.$this->id.'"';
				$this->db->igroupsQuery($query);
				//now take care of the primary
				//first set the former primary to false
				$this->db->igroupsQuery('UPDATE nuggetAuthorMap SET bPrimaryAuthor = 0 WHERE bPrimaryAuthor = 1 AND iNuggetID ="'.$this->id.'"');
				//now set the new primary
				$this->db->igroupsQuery('UPDATE nuggetAuthorMap SET bPrimaryAuthor = 1 WHERE iNuggetID ="'.$this->id.'" AND iAuthorID = "'.$primary.'"');
			}else{
				//N/A
			}
		}
		
		function updateDBNoPrimary(){
			if($this->old != 1){
					$query= 'UPDATE iGroupsNuggets SET sTitle = "'.$this->name->getString().'", sDescription = "'.$this->desc->getString().	'" WHERE iNuggetID = "'.$this->id.'"';
					$this->db->igroupsQuery($query);
					
			}else{
				//N/A
			}
		}

	}
	
	//returns an array of nugget ids based on the group sent to the function
	//The following list of functions applies only to the new nugget design
	function getAllNuggetIDsByGroup( $groupID ){
		//modified to work with both nuggets
		$db = new dbConnection();
		$results = $db->igroupsQuery("(SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID=$groupID) UNION (SELECT iNuggetID FROM ProjectNuggetMap WHERE iProjectID = $groupID)");//if a group is old enough to been around during the original system creation and still around by the same identifier (not likely) this could cause some of their nuggets to be dropped
		$nuggets = array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = $row['iNuggetID'];
		}
		return $nuggets;
	}


	function curNuggetIdByGroupandType($nuggetType, $group){
		$db = new dbConnection();
		$query = "SELECT iNuggetID, dCreated FROM iGroupsNuggets WHERE sTitle = '".$nuggetType."' and iGroupID='".$group->getID()."' ORDER BY dCreated DESC";
		$results = $db->igroupsQuery($query);
		$row = mysql_fetch_array($results);
		return $row[0];
	}
	
	function typeIsActive($nuggetType, $group){
		$db = new dbConnection();
		$query = "SELECT iNuggetID, bOldNugget FROM iGroupsNuggets WHERE sTitle = '".$nuggetType."' and iGroupID='".$group."'";
		$results = $db->igroupsQuery($query);
		$nuggets = array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row['iNuggetID'], $db, $row['bOldNugget']);
		}
		
		foreach($nuggets as $nugget){
			if($nugget->isThisSemester()){
				return true;
			}
		}
		
		return false;
	}
		
	function allByTypeandID($nuggetType, $groupID){
	
		$loop = 0;
		$db = new dbConnection();
		$nuggets = array();
		if($nuggetType == "Other"){
			$query = 'Select iNuggetID FROM iGroupsNuggets Where sTitle not in (\'Abstract\', \'Midterm Report\', \'Final Presentation\', \'Final Report\', \'Code of Ethics\', \'Project Plan\', \'Website\', \'Poster\', \'Team Minutes\') AND iGroupID = "'.$groupID.'"';
		}
		else{
			$query = 'Select iNuggetID FROM iGroupsNuggets Where sTitle ="'.$nuggetType.'" AND iGroupID="'.$groupID.'"';
		}
		$results = $db->igroupsQuery($query);
		while($row = mysql_fetch_array($results)){
			$nuggets[$loop] = new Nugget($row['iNuggetID'], $db);
			$loop += 1;
		}
		return $nuggets;
	}
	
	function allActiveByTypeandID($nuggetType, $groupID, $semester){
		$db = new dbConnection();
		$nuggets = array();
		if($nuggetType == "Other"){
			$query = 'Select iNuggetID FROM iGroupsNuggets Where sTitle not in (\'Abstract\', \'Midterm Report\', \'Final Presentation\', \'Final Report\', \'Code of Ethics\', \'Project Plan\', \'Website\', \'Poster\', \'Team Minutes\') AND iGroupID = "'.$groupID.'" AND iSemesterID = "'.$semester.'"';
		}
		else{
			$query = 'Select iNuggetID FROM iGroupsNuggets Where sTitle ="'.$nuggetType.'" AND iGroupID="'.$groupID.'"';
		}
		$results = $db->igroupsQuery($query);
		while($row = mysql_fetch_array($results)){
			$curNugget = new Nugget($row['iNuggetID'], $db,0);
			$nuggets[] = $curNugget;
		}
		return $nuggets;
	}
	
	function allInactiveByTypeandID($nuggetType, $groupID){
		$db = new dbConnection();
		$nuggets = array();
		if($nuggetType == "Other"){
			$query = 'Select iNuggetID FROM iGroupsNuggets Where sTitle not in (\'Abstract\', \'Midterm Report\', \'Final Presentation\', \'Final Report\', \'Code of Ethics\', \'Project Plan\', \'Website\', \'Poster\', \'Team Minutes\') AND iGroupID = "'.$groupID.'"';
		}
		else{
			$query = 'Select iNuggetID FROM iGroupsNuggets Where sTitle ="'.$nuggetType.'" AND iGroupID="'.$groupID.'"';
		}
		$results = $db->igroupsQuery($query);
		while($row = mysql_fetch_array($results)){
			$curNugget = new Nugget($row['iNuggetID'], $db, 0);
			if(!$curNugget->isThisSemester()){
				$nuggets[] = $curNugget;
			}
		}
		return $nuggets;
	
	}
	
	function allOtherNuggetsByID($groupID){
		$loop = 0;
		$db = new dbConnection();
		$query = 'Select iNuggetID FROM iGroupsNuggets WHERE sTitle not in (\'Abstract\', \'Midterm Report\', \'Final Presentation\', \'Final Report\', \'Code of Ethics\', \'Project Plan\', \'Website\', \'Poster\', \'Team Minutes\')';
		$results = $db->igroupsQuery($query);
		while($row = mysql_fetch_array($results)){
			$nuggets[$loop] = new Nugget($row['iNuggetID'], $db);
			$loop +=1;
		}
		return $nuggets;
	}
	
	
	function testget(){
		$array = getAllNuggetIDsByGroup(202);
		$loop = 0;
		echo count($array);
		while($loop < count($array)){
			echo $array[$loop];
			$loop += 1;
		}
	}
	
	function newNugget($newName, $newDescription, $group){
		global $_DEFAULTNUGGETS;
		$db = new dbConnection();
		$date = date("Y-m-d");
		$query = 'INSERT INTO iGroupsNuggets (sTitle, sDescription, iGroupID, iSemesterID, dCreated) VALUES("'.$newName.'", "'.$newDescription.'", "'.$group->getID().'", "'.$group->getSemester().'", "'.$date.'")';
		$db->igroupsQuery($query);
		return mysql_insert_id();
	}
	
	function removeNugget($nuggetID){
		//remove the nugget
		//remove the nuggetauthormaps
		//remove the fileauthormaps
		$db = new dbConnection();
		$nugget = new Nugget($nuggetID, $db,0);
		$authors = $nugget->getAuthorIDs();
		foreach($authors as $author){
			$nugget->removeAuthor($author);
		}
		
		$files = $nugget->getFileIDs();
		if(count ($files) > 0){
			foreach($files as $file){
				$nugget->removeFile($file);
			}
		}
		$query = 'DELETE FROM iGroupsNuggets WHERE iNuggetID = '.$nuggetID;
		$db->igroupsQuery($query);
	}
	
	function allNuggetsByAuthor($authorID){
		$db = new dbConnection();
		$query = "SELECT iNuggetID FROM NuggetAuthorMap WHERE iAuthorID = $authorID";
		$results = $db->igroupsQuery($query);
		$nuggets= array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row['iNuggetID'], $db);
		}
		return $nuggets;
	}
	
	function allNuggetsByGroup($group){
		$db = new dbConnection();
		$query = "SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID = $group ORDER BY dCreated DESC";
		$results = $db->igroupsQuery($query);
		$nuggets= array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row['iNuggetID'], $db);
		}
		return $nuggets;
	}
	function getNuggetStatus($group, $semester){
	
		$db = new dbConnection();
		$group = $group->getID();
		global $_DEFAULTNUGGETS;
		$nuggets = array();
		foreach($_DEFAULTNUGGETS as $nugType){
			$query = "SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID = $group AND sTitle = '$nugType' AND iSemesterID = $semester";
			$results = $db->igroupsQuery($query);
			if($row=mysql_fetch_array($results)){
				$id = $row['iNuggetID'];
				$nuggets[$nugType] = $id;
			}else{
				$nuggets[$nugType] = 0;
			}
		}
		return $nuggets;
	}
	
	//END NEW NUGGET FUNCTIONS
	//The following are functions usable on only old nuggets
	function getOldNuggetsByGroup($group){
		$db = new dbConnection();
		$query = "SELECT iNuggetID FROM ProjectNuggetMap WHERE iProjectID = $group";
		$results = $db->igroupsQuery($query);
		$nuggets = array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[0], $db, 1);
		}
		return $nuggets;
	}

	function getOldNuggetsByGroupAndSemester($group, $semester) {
		$db = new dbConnection();
		$group = $group->getID();
		$query = "SELECT iNuggetID FROM ProjectNuggetMap WHERE iProjectID=$group AND iSemesterID=$semester";
		$results = $db->igroupsQuery($query);
		$nuggets = array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[0], $db, 1);
		}
		return $nuggets;
	}

	//The following are functions usable on both old and new nuggets
	function allNuggetsByDate(){
		$db = new dbConnection();
		$query = "(SELECT dCreated, iNuggetID, bOldNugget FROM iGroupsNuggets) UNION (SELECT dCreated, iID, bOldNugget FROM Nuggets) ORDER BY dCreated DESC";
		$results = $db->igroupsQuery($query);
		$nuggets= array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[1], $db, $row[2]);
		}
		return $nuggets;
	}
	
	function testAllByDate(){
		$nug = allNuggetsByDate();
		foreach ($nug as $nug){
			print $nug->getType();
			print "<br>";
		}
	}
	
	function allByName(){
		$db = new dbConnection();
		$query = "(SELECT dCreated, iNuggetID, bOldNugget, sTitle FROM iGroupsNuggets) UNION (SELECT dCreated, iID, bOldNugget, sTitle FROM Nuggets) ORDER BY sTitle";
		$results = $db->igroupsQuery($query);
		$nuggets= array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[1], $db, $row[2]);
		}
		return $nuggets;

	}	

	function allByDescription(){
		$db = new dbConnection();
		$query = "(SELECT sDescription, dCreated, iNuggetID, bOldNugget, sTitle FROM iGroupsNuggets) UNION (SELECT sAbstract, dCreated, iID, bOldNugget, sTitle FROM Nuggets) ORDER BY sDescription";
		$results = $db->igroupsQuery($query);
		$nuggets= array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[2], $db, $row[3]);
		}
		return $nuggets;
	}

	function nuggetAuthorList(){
		//searches the database for all unique authors who have authored a nugget, puts them in abc order and links them to their group
		$db = new dbConnection();
		$query = "(SELECT iAuthorID FROM nuggetAuthorMap) UNION (SELECT iPersonID FROM PeopleNuggetMap)";
		$results = $db->igroupsQuery($query);
		$authors = array();
		while($row = mysql_fetch_array($results)){
			$authors[] = new Person($row[0], $db);
			$temp = new Person($row[0], $db);
		}
		$namesID = array();
		foreach ($authors as $author){
			$namesID[$author->getLastName()]=$author->getID();
		}
		ksort($namesID);
		$final = array();
		foreach($namesID as $per=>$id){
			$final[] = new Person($id, $db);
		}
		//currently starts at a null person remove him to get things going
		array_shift($final);
		return $final;
	}
	
	function nuggetIproList(){
		$db = new dbConnection();
		$query = "SELECT iProjectID ,iSemesterID FROM ProjectSemesterMap ORDER BY iSemesterID";
		$results = $db->igroupsQuery($query);
		$projects = array();
		
		while($row = mysql_fetch_array($results)){
			$projects[] = new Group($row[0],0,$row[1],$db);
		}
		return $projects;
	}

	function getNuggetsByGroup($groupID){
		$db = new dbConnection();
		$query = "SELECT iNuggetID FROM ProjectNuggetMap WHERE iProjectID = $groupID";
		$results = $db->igroupsQuery($query);
		$nuggets = array();
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[0], $db, 1);
		}
		$query = "SELECT iNuggetID FROM iGroupsNuggets WHERE iGroupID = $groupID";
		$results = $db->igroupsQuery($query);
		while($row = mysql_fetch_array($results)){
			$nuggets[] = new Nugget($row[0], $db, 0);
		}
		return $nuggets;
	}
	//END MIXED NUGGET FUNCTIONS
}
?>
