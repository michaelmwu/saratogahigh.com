<? // db.php | icyhandofcrap

if(USE_DB)
{
	// Connecting, selecting database
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
	   or die("Could not connect : " . mysql_error());
	
	mysql_select_db(DB_DATABASE) or die("Could not select database");
	
	class db {
		function get_prefix_id($table,$id,$extrawhere = "",$extraselect = "",$joins = "")
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
			return db::fetch_row( $row );
		}
	
		function get_prefix_row($table,$where,$extraselect = "",$joins = "")
		{
			return db::get_row($table . "_LIST",$where,$extraselect,$joins);
		}
		
		function get_row($table,$where,$extraselect = "",$joins = ""){
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .= " WHERE $where";
			$row = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return db::fetch_row( $row );
		}
	
		function get_prefix_result($table,$where,$order="",$extraselect = "",$joins = "")
		{
			return db::get_result($table . "_LIST",$where,$order,$extraselect,$joins);
		}
		
		function get_result($table,$where,$order="",$extraselect = "",$joins = "")
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
		
		function fetch_row($result) {
			return mysql_fetch_array( $result, MYSQL_ASSOC );
		}
	
		function prefix_update($table,$values,$where) 
		{
			db::update($table . "_LIST",$values,$where);
		}
		
		function update($table,$values,$where) 
		{
			$statement = "SELECT * FROM $table WHERE $where";
			$result = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			$update = db::fetch_row( $result );
			
			$autoquote = true;
			
			if(array_key_exists('autoquote',$values) && $values['autoquote'] == false)
			{
				$autoquote = false;
				unset($values['autoquote']);
			}
			
			$statement = ($update ? "UPDATE" : "INSERT INTO") . " $table SET ";
			$first = true;
			foreach($values as $key => $value)
			{
				if(!$first)
					$statement .= ", ";
				if($autoquote)
					$statement .= "$key = '$value'";
				else
					$statement .= "$key = $value";
				$first = false;
			}

			if($update)
				$statement .= " WHERE $where";
			mysql_query($statement) or die($statement . "<br>" . mysql_error());
			
			return ($update ? 0 : mysql_insert_id());
		}
		
		function prefix_delete($table,$where)
		{
			db::delete($table . "_LIST",$where);
		}
		
		function delete($table,$where)
		{
			$statement = "DELETE FROM $table WHERE $where";
			mysql_query($statement) or die($statement . "<br>" . mysql_error());
		}
		
		function free(&$result)
		{
			mysql_free_result($result);
		}
	}
}
?>
