<?php namespace ProcessWire;

/**
 * Handles creation, storage and retrieval of file tokens
 * used to authorize the WOPI client (Collabora) for file
 * access.
 *
 * Code by BitPoet
 * Licensed under MPL 2.0
 */
class LoolToken extends WireData {

	const dbTableName = "lool_tokens";
	
	public function getFileDataForToken($token) {

		$sql = "SELECT * FROM " . self::dbTableName . " WHERE token=" . $this->database->quote($token);
		$result = $this->database->query($sql);

		if(! $result) {
			return FALSE;
		}

		if(! $row = $result->fetch(\PDO::FETCH_ASSOC)) {
			return FALSE;
		}

		return $row;
	}

	
	public function getFileToken($pageid, $fieldname, $filename) {

		$sql = "SELECT token FROM " . self::dbTableName . " WHERE pages_id = " . $this->sanitizer->int($pageid) . " AND filename=" . $this->database->quote($filename);
		
		 $result = $this->database->query($sql);
		 
		 if(! $result) {
		 	return FALSE;
		 }
		 
		 if(! $row = $result->fetch(\PDO::FETCH_ASSOC)) {
		 	return FALSE;
		 }

		return $row["token"];		 
	}
	
	
	public function createToken($pageid, $fieldname, $filename, $username) {
		$db = $this->database;
		
		$fileid = bin2hex($pageid . "#" . $filename);
		$token =  $this->getRandomString($fileid);
		
		$sql =	"INSERT INTO " . self::dbTableName .
				" ( pages_id, fieldname, filename, username, fileid, token ) " .
				" VALUES " .
				" (" . ((int)$pageid) . ", " . $db->quote($fieldname) . ", " . $db->quote($filename) . ", " . $db->quote($username) . ", " . $db->quote($fileid) . ", " . $db->quote($token) . " )"
		;
		$db->exec($sql);
		
		return $token;
	}
	
	private function getRandomString($inp) {
		if(function_exists("random_bytes")) {
			return bin2hex(random_bytes(10));
		}
		$random = "";
		for($i = 0; $i < 10; $i++) {
			$random .= chr(mt_rand(1,$i));
		}
		return bin2hex($random);
	}
	
	public function createTable() {
		$sql =	"CREATE TABLE IF NOT EXISTS " . self::dbTableName . " ( " .
				" pages_id int not null, " .
				" fieldname varchar(255), " .
				" filename varchar(255), " .
				" username varchar(100), " .
				" fileid varchar(255), " .
				" token varchar(100), " .
				" primary key (pages_id, filename), " .
				" unique key (fileid), " .
				" index (token) " .
				") " .
				" CHARACTER SET=" . $this->config->dbCharset .
				" ENGINE=" . $this->config->dbEngine
		;
		
		$this->database->exec($sql);
	}
	
	public function removeTable() {
		$sql = "DROP TABLE IF EXISTS " . self::dbTableName;
		$this->database->exec($sql);
	}
	
}
