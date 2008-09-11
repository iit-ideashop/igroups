<?php
include_once( "superstring.php" );

// A single TODO item class;
if ( !class_exists( "Todo" ) ) {
	class Todo {
		var $id, $task, $group, $type,$semester, $assigned, $taskNum, $priority, $complete,$dueDate;
		var $db;
		
		function Todo( $id, $db ) {
			if ( $temp = mysql_fetch_array( $db->igroupsQuery( "SELECT * FROM TodoList WHERE iID=$id" ) ) ) {
				$this->id = $id; //iID in DB
				$this->task = $temp['sTask']; // task description
				$this->group = $temp['iGroupID']; // igroup ID
				$this->type = $temp['iGroupType']; // type of iGROUP
				$this->semester = $temp['iSemesterID']; // semester ID (for this item)
				$this->assigned = $temp['iAssignedID']; // id of person assigned to this
				$this->taskNum = $temp['iTaskNum']; // task number in the todolist
				$this->priority = $temp['iPriority']; // priority, how important it is
				$this->complete = $temp['bComplete']; // is is compelte? '1' means it is!
				$this->dueDate = $temp['dDueDate']; // is is compelte? '1' means it is!
				$this->db = $db;
			}
		}
		
		// Return the iID from the DB
		function getID() {
			return $this->id;
		}

		// Return the ID of the person who is assigned to this
		function getAssignedID(){
			return $this->assigned;
		}

		// Return the actual person (class) who is assigned to this item, if he/she exists
		function getAssigned(){
	    if($this->assigned != -1)
		return new Person($this->assigned, $this->db);
	    else
		return null;
		}

		// Return the assigned persons name
		function getAssignedName(){
	    if($this->assigned != -1){
		$bob = new Person($this->assigned, $this->db);
		if($bob)
		    return $bob->getLastName().",&nbsp;".$bob->getFirstName();
		else
		    return "";
	    }
			else
				return "";
		}

		// Returns the task description (the actual words and stuff)
		function getTask() {
			return $this->task;
		}

		// Returns the actual group
		function getGroup() {
			return new Group( $this->getGroupID(), $this->getGroupType(), $this->getSemester(), $this->db );
		}

		// Return group iID
		function getGroupID() {
			return $this->group;
		}
		
		// Return group type
		function getGroupType() {
			return $this->type;
		}
		
		// Return due date
		function getDueDate() {
			if($this->dueDate){
		if($this->dueDate != "1969-12-31 00:00:00" && $this->dueDate != "0000-00-00 00:00:00"){
					$temp = explode( "-", $this->dueDate );
					return date( "m/d/Y", mktime( 0, 0, 0, $temp[1], $temp[2], $temp[0] ) );
				}
			}
			// else fail and return nothing
			return null;
		}
		// Return the summer for this item
		function getSemester() {
			return $this->semester;
		}

		// return Task Number
		function getTaskNum() {
			return $this->taskNum;
		}

		// decreate the task number (used in conjunction with delete)
		function decramentTaskNum(){
			$this->taskNum--;
			$sql = "UPDATE TodoList SET iTaskNum='".$this->taskNum."' WHERE iID='".$this->id."'";
			$this->db->igroupsQuery($sql);
		}

		// Return Completion Status: '0' for none '1' for true!
		function getCompleted() {
			return $this->complete;
		}

		// Switches between complete and .. not!
		function toggleComplete() {
			if ($this->complete == 0)
				$this->complete = 1;
			else
				$this->complete = 0;
			$sql = "UPDATE TodoList SET bComplete='".$this->complete."' WHERE iID='".$this->id."'";
			$this->db->igroupsQuery($sql);
		}

		// Returns the Priority (importance) on this task
		function getPriority() {
			return $this->priority;
		}

	function update($tid,$task,$assigned, $date){
	    $this->taskNum = $tid;
	    $this->task = $task;
	    $this->assigned = $assigned;
	    $dbdate = null;
	    if($date != null){
		$temp = explode( "/", $date );
		$dbdate = date( "Y-m-d", mktime(0,0,0,$temp[0],$temp[1],$temp[2] ) );
	    }
	    else
		$dbdate = "0000-00-00 00:00:00";
	    $this->dueDate = $dbdate;
	    $sql = "UPDATE TodoList SET sTask=\"".$task."\", iTaskNum=\"".$tid."\",iAssignedID=\"".$assigned."\",dDueDate=\"".$dbdate."\" where iID=".$this->id;
	    $this->db->igroupsQuery($sql);
	}
	}
}
?>
