<?php namespace ProcessWire;

/**
 * Handles WOPI requests and file locks for the Collabora CODE
 * WOPI client.
 *
 * Code by BitPoet
 * Licensed under MPL 2.0
 */
class LoolWopi extends WireData {
	
	const lockTableName = "lool_locks";
	
	public function parseRequest() {
		$segments = $this->input->urlSegments();
		
		if(! count($segments)) {
			return array("error" => "HTTP/1.1 400 Illegal URL path");
		}
		
		if($segments[1] == "files")	{
			// files endpoint
			$ret = array("endpoint" => "file", "action" => "NotImplemented");

			if(count($segments) == 1) {
				// we need at least a file id in the URL
				$ret["error"] = "Missing argument for files endpoint";
				return $ret;
			}
			
			$ret["fileid"] = $segments[2];
			
			if(! isset($segments[3])) {
				// operation is one of: CheckFileInfo, PutRelativeFile, Lock, Unlock, RefreshLock, UnlockAndRelock, DeleteFile or RenameFile
				if($_SERVER["REQUEST_METHOD"] == "GET") {
					$ret["action"] = "CheckFileInfo";
					return $ret;
				}
				// all other file operations are made through POST requests
				if($_SERVER["REQUEST_METHOD"] != "POST") {
					$ret["action"] = "NotImplmented";
					return $ret;
				}

				switch($_SERVER["HTTP-X-WOPI-OVERRIDE"]) {
					/* Deactivated for now, but will soon be implemented
					case "PUT_RELATIVE":
						$ret["action"] = "PutRelativeFile";
						break;
					*/
					case "LOCK":
						if(isset($_SERVER["HTTP-X-WOPI-OLDLOCK"])) {
							$ret["action"] = "UnlockAndRelock";
							$ret["lock"] = $_SERVER["HTTP-X-WOPI-OLDLOCK"];
						} else {
							$ret["action"] = "Lock";
							$ret["lock"] = isset($_SERVER["HTTP-X-WOPI-LOCK"]) ? $_SERVER["HTTP-X-WOPI-LOCK"] : "";
						}
						break;
					case "UNLOCK":
						$ret["action"] = "Unlock";
						break;
					case "REFRESH_LOCK":
						$ret["action"] = "RefreshLock";
						break;
					default:
						break;
				}
				
			} else {
				// either contents endpoint or illegal
				if($segments[3] != "contents") {
					$ret["error"] = "Unknown files endpoint " . $segments[2];
					return $ret;
				}
				
				if($_SERVER["REQUEST_METHOD"] == "GET") {
					
					$ret["action"] = "GetFile";
					return $ret;
					
				} else if($_SERVER["REQUEST_METHOD"] == "POST") {
					
					$ret["action"] = "PutFile";
					return $ret;
					
				} else {
					
					$ret["error"] = "Illegal request method for endpoint files/content";
					return $ret;
					
				}
				
				$ret["error"] = "Unknown operation";
				return $ret;
			}
			
		} else if($segments[1] == "containers") {
			// containers endpoint
			$ret = array("endpoint" => "containers", "action" => "NotImplemented");
			return $ret;
		} else if($segments[1] == "ecosystem") {
			// ecosystem endoints
			$ret = array("endpoint" => "ecosystem", "action" => "NotImplemented");
			return $ret;
		}
	
		return array("error" => "Unknown endpoint");
	}
	
	
	public function getLock($fileid) {
		$db = $this->database;
		
		$sql =	"SELECT *, (CASE WHEN " .
				"lock_update_time = 0 THEN " .
				"TIMESTAMPDIFF(SECOND, lock_create_time, NOW()) ELSE " .
				"TIMESTAMPDIFF(SECOND, lock_update_time, NOW()) " .
				"END) as age " .
				" FROM " . self::lockTableName .
				" WHERE fileid = " . $db->quote($fileid);
		
		$ret = $db->query($sql);
		
		if(! $ret) return false;
		
		if(! $row = $ret->fetch(\PDO::FETCH_ASSOC)) return false;
		
		// Expire locks after 30 minutes = 1800 seconds
		if($row["age"] > 1800) {
			$this->unLock($fileid);
			return false;
		}
		
		return $row;
	}
	
	
	public function lock($fileid, $user) {
		$db = $this->database;
		
		$sql =	"INSERT INTO " . self::lockTableName . " ( " .
				" fileid, lock_user_id " .
				" ) VALUES ( " .
				$db->quote($fileid) . ", " . $user->id .
				")";
		
		$db->exec($sql);
	}
	
	public function unLock($fileid) {
		$db = $this->database;
		
		$sql =	"DELETE FROM " . self::lockTableName . " WHERE fileid = " . $db->quote($fileid);
		$db->exec($sql);
	}
	
	public function updateLock($fileid) {
		$db = $this->database;

		$sql =	"UPDATE " . self::lockTableName . " SET lock_update_time = NOW()";
		$db->exec($sql);
	}
	
	public function createLocksTable() {
		$sql =	"CREATE TABLE IF NOT EXISTS " . self::lockTableName . " ( " .
				" id int unsigned not null auto_increment, " .
				" fileid varchar(100), " .
				" lock_user_id int, " .
				" lock_create_time timestamp default current_timestamp, " .
				" lock_update_time timestamp default 0, " .
				" primary key(fileid) " .
				") " .
				" CHARACTER SET=" . $this->config->dbCharset .
				" ENGINE=" . $this->config->dbEngine
		;
		$this->database->exec($sql);
	}
	
	public function removeLocksTable() {
		$sql = "DROP TABLE IF EXISTS " . self::lockTableName;
		$this->database->exec($sql);
	}
	
	public function addLockIdColumn() {
		$sql = "ALTER TABLE " . self::lockTableName . " ADD COLUMN id int unsigned not null auto_increment FIRST, ADD KEY(id)";
		$this->database->exec($sql);
	}
}
