<?php

# a todo list wrapper class
include_once( "superstring.php" );
include_once( "todo.php" );

if ( !class_exists( "TodoList" ) ) {
	class TodoList {
		var $todolist, $length,$id,$taskTop,$semester;
		var $db;
		
		function TodoList( $id,$sem, $db ) {
			$this->db = $db;
			$this->id = $id;
			$this->todolist = array();
			$this->taskTop = 0;
			$this->semester = $sem;
			$sql = "SELECT iID FROM TodoList WHERE iGroupID='".$id."' AND iSemesterID='".$sem."' ORDER BY iTaskNum";
			$result = $db->igroupsQuery($sql);
			while($row = mysql_fetch_array($result))
				$this->todolist[] = new Todo($row['iID'],$db);
			$this->length = count($this->todolist);
			if($this->length > 0)
				$this->taskTop = $this->todolist[$this->length -1]->getTaskNum();
		}

		function getList(){
			return $this->todolist;
		}

		function getSortedList($sort) {
			$query = $this->db->igroupsQuery("SELECT iID FROM TodoList WHERE iGroupID={$this->id} AND iSemesterID={$this->semester} ORDER BY $sort");
			$sortedTodo = array();
			while ($row = mysql_fetch_array($query))
				$sortedTodo[] = new Todo($row['iID'], $this->db);
			return $sortedTodo;
		}

        function getTask($num){
            return $this->todolist[$num-1];
        }

		function getLength(){
			return $this->length;
		}

		# return the highest TaskNum
		function getTaskNum(){
			return $this->taskTop;
		}

		function updateCompleteStatus($taskNum){
			$this->todolist[$taskNum -1]->toggleComplete();
		}

        function fixdate($date){
			$my_t = getdate(date("U"));
			if(strcasecmp($date,'today') == 0){
				$dateTemp = $my_t['mon']."/".$my_t['mday']."/".$my_t['year'];
			}
			else if(strcasecmp($date,'tomorrow') == 0){
				$my_t = getdate(date("U")+(60*60*24));
				$dateTemp = $my_t['mon']."/".$my_t['mday']."/".$my_t['year'];
			}
            else{
                $dateTemp = $date;
                $inputDate = explode('/',$dateTemp);
                // if the exploded object has 3 parts (basically is it a ['one','two','three'] array now)
                if(count($inputDate) != 3)
                    $ERROR_date = 1;
                else{ // now we assume that it is 3 parts
                    // get the year
                    $year = date('y');
                    // if the given year was given 2006 instead of '06'
                    if(strlen($inputDate[2]) == 4)
                        $year = $my_t['year'];
                        
                    // get day
                    $day = $my_t['mday'];
                    
                    // get month
                    $month = $my_t['mon'];
                    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);
                    // check to see if entered date is before today or not (not is good)
                    // and also check legallity of it
                    if($inputDate[2] >= $year){
                        if ($year == $inputDate[2]){
                            if($inputDate[0] == $month){
                                if($inputDate[1] < $day || $inputDate[1] > $days_in_month){
                                    $ERROR_date = 1;
                                }
                            }
                            else if($inputDate[0] > $month && $inputDate[0] <= 12){
                                if ($inputDate[1] < 0 || $inputDate[1] > $days_in_month){
                                    $ERROR_date = 1;
                                }
                            }
                            else {

                                $ERROR_date = 1;

                            }
                        }
                        else{ // greater than this year
                            if ($inputDate[0] < 0 || $inputDate[0] > 12){
                                $ERROR_date = 1;
                            }
                            else {
                                if($inputDate[1] < 0 || $inputDate[1] > $days_in_month){
                                    $ERROR_date = 1;
                                }
                            }
                        }
                    }
                    else
                        $ERROR_date = 1;
                } 
            }
            if($ERROR_date == 1)
                return null;
            else
                return $dateTemp;
		}

		function addTask($task,$date,$person){
			$this->taskTop += 1;

            $dateTemp = $this->fixdate($date);
            if($dateTemp != null){
                $temp = explode( "/", $dateTemp );
                $dbdate = date( "Y-m-d", mktime(0,0,0,$temp[0],$temp[1],$temp[2] ) );
            }
            else
                $dbdate = "0000-00-00 00:00:00";
			$sql = "INSERT INTO TodoList (sTask,iGroupID,iTaskNum,iSemesterID,iAssignedID,dDueDate) VALUES('".$task."','".$this->id."','".$this->taskTop."','".$this->semester."','".$person."','".$dbdate."')";
			$this->db->igroupsQuery($sql);
			$inID = $this->db->igroupsInsertID();
			$this->todolist[] = new Todo($inID,$this->db);
			$this->length += 1;
		}

		function deleteTask($taskID, $taskNumber){
			// precheck for descreaing taskNumbers IFF delete item actually exists
			$sql = "SELECT * from TodoList where iID='".$taskID."' and iTaskNum='".$taskNumber."'";
			$result = $this->db->igroupsQuery($sql);

			// If delete was sucessful do the following else, meh
			if (mysql_num_rows($result) > 0 ){
				// Do actual delete
				$sql = "DELETE FROM TodoList where iID='".$taskID."' and iTaskNum='".$taskNumber."'";
				$this->db->igroupsQuery($sql);

				// For each item in the todolist after the item we are deleting, decrease it's task# count by one
				for($i = $taskNumber; $i < $this->length ; $i++){
					$this->todolist[$i]->decramentTaskNum();
				}
				// remove this actual item from the list!
				// This is done last due to syncronization issues
				unset($this->todolist[$taskNumber-1]);
				$this->length -= 1;
			}
			else { // nothing deleted
			// so do nothing
			}
		}

        function updateTask($iid, $tid, $task, $worker,$date) {
            $this->todolist[$tid-1]->update($tid,$task,$worker,$date);
        }
	}
}
?>
