<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */



/**
 * MySQL interface
 */
class MySQLCore extends Db
{
	protected $linktoDB;
	
	function __construct(){
		try{
			$this->linktoDB = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPASSWORD);
			$this->linktoDB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$this->linktoDB->query("SET NAMES 'utf8'");
			return $this->linktoDB;
		}
		catch(Exception $e){
			Tools::debugException($e);
		}
		
	}
	
	/**
	* Insert data in the DB. All inputs data are sanitize with the "prepare" PDO function.
	* @param String $table The name of the table where the data will be inserted in $key => $data format
	* @param Array $array An array containing all the data
	* @return Bool Query executed or not
	*/	
	public function Insert($table, $array){
		unset($array['submit']);
		unset($array['token']);
		$q = $q2 = '';
		foreach( $array as $key => $value ){
			$q  .= "$key,";			
			$q2 .= ":$key,";
		}
		$q = substr($q,0,strlen($q)-1);
		$q2 = substr($q2,0,strlen($q2)-1);
		$query = "INSERT INTO ".$table."(".$q.") VALUES(".$q2.")";
		$rslt = $this->linktoDB->prepare($query);
		
		foreach($array as $key => $value ){
			$rslt->bindValue(":".$key, $value);
		}
		
		try {
			$rslt->execute();
			return $this->linktoDB->lastInsertId();
			
		} catch(PDOException $e) {
			if( SQL_DEBUG == true )
				Tools::debugException($e);
			
			return false;
		}
		
	}
	

	
	/**
	* Select request in DB
	* @param String $rq Request
	* @param Array $param Elements for WHERE condition
	* @param STRING $orderby Order the results by 
	* @return Array Array of results returned by PDO
	*/
	public function Select($rq, $param='', $orderby=''){
		try {
			
			$rslt = $this->linktoDB->prepare($rq);
			if( !empty($param) && is_array($param) ){
				foreach($param as $key => $value){
					$rslt->bindValue(':'.$key, $value);	
				}
			}
			
			$rslt->execute();
			return $rslt->fetchAll(PDO::FETCH_ASSOC);
			
		} catch(PDOException $e) {
			
			if( SQL_DEBUG == true ) {
				echo $rq.'<br /><br />';
				Tools::debugException($e);
			}
			return false;
		}
	}
	

	/**
	 * Get a field value
	 * @param String $rq Request
	 * @param Array $param Elements for WHERE condition
	 * @return String The value
	 */
	public function getValue($rq, $param=''){
		$rslt = $this->linktoDB->prepare($rq);
		if( !empty($param) && is_array($param) ){
			foreach($param as $key => $value){
				$rslt->bindValue(':'.$key, $value);	
			}
		}
		
		try {
			$rslt->execute();
			$out = $rslt->fetch(PDO::FETCH_BOTH);
			return $out[0];
			
		} catch(PDOException $e) {
			if( SQL_DEBUG == true )
				Tools::debugException($e);
			return false;
		}
		
	}
	
	
	

	/**
	 * Get a complete line of data
	 * @param String $rq Request
	 * @param Array $param Elements for WHERE condition
	 * @return Array Array with key = column name
	 */
	public function getRow($rq, $param=''){
		$rslt = $this->linktoDB->prepare($rq);
		if( !empty($param) && is_array($param) ){
			foreach($param as $key => $value){
				$rslt->bindValue(':'.$key, $value);	
			}
		}
		
		try {
			$rslt->execute();
			return $rslt->fetch(PDO::FETCH_ASSOC);
			
		} catch(PDOException $e) {
			if( SQL_DEBUG == true )
				Tools::debugException($e);
			
			return false;
		}
	}
	
	
	/**
	* Update data in the DB. All inputs data are sanitize with the "prepare" PDO function.
	* @param String $table The name of the table where the data will be updated
	* @param Array $array An array containing all the data
	* @param Array $where This contains the "where" conditions. fe : where[0] => attributes, where[1] => value
	* @return Bool Query executed or not
	*/
	public function UpdateDB($table, $array, $where){
		unset($array['submit']);
		unset($array['token']);
		$q = '';
		foreach( $array as $key => $value ){
			$q  .= "$key=:$key,";
		}
		$q = substr($q,0,strlen($q)-1);
		$query = "UPDATE ".$table." SET ".$q."";
		
		if(is_array($where) && count($where)>0 ){
			$wa = " WHERE ";
			foreach($where as $key => $value ){
				$query .= $wa." ".$key."=:where_".$key." ";
				$wa = " AND ";
			}
		}elseif($where){
			$query .= " ".$where;	
		}
		
		$rslt = $this->linktoDB->prepare($query);
		
		foreach($array as $key => $value ){
			$rslt->bindValue(":".$key, $value);
		}
		
		if(is_array($where) && count($where)>0){
			foreach($where as $key => $value ){
				$rslt->bindValue(":where_".$key, $value);
			}
		}
		
		try {
			$rslt->execute();
			return true;
			
		} catch(PDOException $e) {
			var_dump($query);
			if( SQL_DEBUG == true )
				Tools::debugException($e);
			
			return false;
		}
		
		
	}
	
	/**
	* Delete date in DB.
	* @param String $table The name of the table where the data will be updated
	* @param Array $where This contains the "where" conditions. fe : where[0] => attributes, where[1] => value
	* @return Bool Query executed or not
	*/
	public function Delete($table, $where){
		$query = "DELETE FROM $table ";
		
		$wa = " WHERE ";
		
		if(is_array($where) && count($where)>0 ){
			foreach($where as $key => $value ){
				$query .= $wa." ".$key."=:".$key." ";
				$wa = " AND ";
			}
		}elseif($where){
			$query .= " ".$where;	
		}
		
		$rslt = $this->linktoDB->prepare($query);
		foreach($where as $key => $value ){
			$rslt->bindValue(":".$key, $value);
		}
		
		try {
			$rslt->execute();
			return true;
			
		} catch(PDOException $e) {
			if( SQL_DEBUG == true )
				Tools::debugException($e);
			
			return false;
		}
	}
	
	
	/**
	* Request as INSERT ... ON DUPLICATE KEY UPDATE
	* @param string $table Table name
	* @param array $insert Array containing data to insert
	* @param array $update Array containing data to update if the primary key already exists
	* @return bool True or false
	*/
	public function Merge($table, $insert, $update)
	{
		if(count($insert)>0 && count($update)>0)
		{
			$rq="INSERT INTO ".$table;
			foreach($insert as $key => $val)
			{
				$tabKey[] = $key;
				$tabVal[] = ":".$key;
				$tabBind[$key] = $val;
			}
			$listeKey = implode(",", $tabKey);
			$listeVal = implode(",", $tabVal);
			
			
			foreach($update as $key => $val)
			{
				$tabU[] = $key." = :".$key;
				$tabBind[$key] = $val;
			}
			$listeU = implode(",", $tabU);
			
			$rq.="(".$listeKey.") VALUES (".$listeVal.") ON DUPLICATE KEY UPDATE ".$listeU;
			$rslt = $this->linktoDB->prepare($rq);
			
			foreach($tabBind as $key => $value ){
				$rslt->bindValue(":".$key, $value);
			}
			
			try {
				$rslt->execute();
				return $this->linktoDB->lastInsertId();
				
			} catch(PDOException $e) {
				if( SQL_DEBUG == true ){
					Tools::debugException($e);
				}
				return false;
			}
			
		}

	}
	
		
	/** 
	* Execute an SQL query
	* @param String $query SQL query
	* @return Object Depends on query
	*/
	public function query($query){
		return $this->linktoDB->exec($query);
	}	
	
	
	/**
	* Check if a table exists in DB
	* @param String $tableName The table name
	* @return Bool If true table exists
	*/
	public function tableExists($tableName){
		return $this->linktoDB->query("SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_name = '".Tools::cleanSQL($tableName)."'")->fetch();	
	}
	
	
	/**
	* Check if a column exists in DB
	* @param String $tableName The table name
	* @param String $columnName The column name
	* @return Bool If true column exists
	*/
	public function columnExists($tableName, $columnName){
		return $this->linktoDB->query("SHOW COLUMNS FROM ".Tools::cleanSQL($tableName)." WHERE Field = '".Tools::cleanSQL($columnName)."'");
	}
	
	
	/**
	* Get the specific table columns name 
	* @param String $tableName The table name
	* @return Array The columns name
	*/
	public function getColumns($tableName){
		$rslt = $this->linktoDB->prepare("SHOW COLUMNS FROM ".Tools::cleanSQL($tableName)." ");
		$rslt->execute();
		return $rslt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	
}

?>