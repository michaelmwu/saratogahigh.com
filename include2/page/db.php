<? // db.php | icyhandofcrap

if(USE_DB)
{
	// Connecting, selecting database
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
	   or die("Could not connect : " . mysql_error());
	
	mysql_select_db(DB_DATABASE) or die("Could not select database");
	
	class db {
		public function get_prefix_id($table,$id,$extrawhere = "",$extraselect = "",$joins = "")
		{
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table" . "_LIST";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .=" WHERE " . $table . "_ID='" . $id . "'";
			if(strlen($extrawhere > 0))
				$statement .= " AND $extrawhere";
			$row = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return mysql_fetch_array( $row, MYSQL_ASSOC );
		}
	
		public function get_prefix_row($table,$where,$extraselect = "",$joins = "")
		{
			return db::get_row($table . "_LIST",$where,$extraselect,$joins);
		}
		
		public function get_row($table,$where,$extraselect = "",$joins = ""){
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .= " WHERE $where";
			$row = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return mysql_fetch_array( $row, MYSQL_ASSOC );
		}
	
		public function get_prefix_result($table,$where,$order="",$extraselect = "",$joins = "")
		{
			return db::get_result($table . "_LIST",$where,$order,$extraselect,$joins);
		}
		
		public function get_result($table,$where,$order="",$extraselect = "",$joins = "")
		{
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .= " WHERE $where";
			if(strlen($order) > 0)
				$statement .= " ORDER BY $order";
			$result = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return $result;
		}
		
		public function fetch_row($result) {
			return mysql_fetch_array( $result, MYSQL_ASSOC );
		}
	
		public function prefix_update($table,$values,$where,$extraset = "") 
		{
			$statement = "SELECT * FROM $table" . "_LIST WHERE $where";
			$result = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			if($row = mysql_fetch_array($result, MYSQL_ASSOC) )
			{
				$first = true;
				$statement = "UPDATE $table" . "_LIST SET ";
				foreach($values as $key => $value)
				{
					if(!$first)
						$statement .= ",";
					$statement .= $table . "_" . "$key = '$value'";
					$first = false;
				}
				if(strlen($extraset > 0))
					$statement .= ", $extraset";
				$statement .= " WHERE $where";
				mysql_query($statement) or die($statement . "<br>" . mysql_error());
				return 0;
			}
			else
			{
				$first = true;
				$statement = "INSERT INTO $table" . "_LIST SET ";
				foreach($values as $key => $value)
				{
					if(!$first)
						$statement .= ",";
					$statement .= $table . "_" . "$key = '$value'";
					$first = false;
				}
				if(strlen($extraset) > 0)
					$statement .= ", $extraset";
				$statement .= " WHERE $where";
				mysql_query($statement) or die($statement . "<br>" . mysql_error());
				return mysql_insert_id();
			}
		}
		
		public function update($table,$values,$where,$extraset = "") 
		{
			$statement = "SELECT * FROM $table WHERE $where";
			$result = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			if($row = mysql_fetch_array($result, MYSQL_ASSOC) )
			{
				$first = true;
				$statement = "UPDATE $table SET ";
				foreach($values as $key => $value)
				{
					if(!$first)
						$statement .= ",";
					$statement .= "$key = '$value'";
					$first = false;
				}
				if(strlen($extraset) > 0)
					$statement .= ", $extraset";
				$statement .= " WHERE $where";
				mysql_query($statement) or die($statement . "<br>" . mysql_error());
				return 0;
			}
			else
			{
				$first = true;
				$statement = "INSERT INTO $table SET ";
				foreach($values as $key => $value)
				{
					if(!$first)
						$statement .= ",";
					$statement .= "$key = '$value'";
					$first = false;
				}
				if(strlen($extraset > 0))
					$statement .= ", $extraset";
				mysql_query($statement) or die($statement . "<br>" . mysql_error());
				return mysql_insert_id();
			}
		}
		
		public function prefix_delete($table,$where)
		{
			db::delete($table . "_LIST",$where);
		}
		
		public function delete($table,$where)
		{
			$statement = "DELETE FROM $table WHERE $where";
			mysql_query($statement) or die($statement . "<br>" . mysql_error());
		}
	}
}
?>