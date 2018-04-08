<?php namespace ProcessWire;

class LoolWopi extends WireData {
	
	const lockTableName = "lool_locks";
	
	public function parseRequest() {

		$segments = $this->input->urlSegments();
		
		if(! count($segments)) {
			return array("error" => "HTTP/1.1 400 Illegal URL path");
		}
		
		$this->log->message("Segments: " . print_r($segments, true));

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
					case "LOCK":
						$ret["action"] = "Lock";
						break;
					case "UNLOCK":
						$ret["action"] = "Unlock";
						break;
					case "REFRESH_LOCK":
						$ret["action"] = "RefreshLock";
						break;
					*/
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
	
	public function createLocksTable() {
		$sql =	"CREATE TABLE IF NOT EXISTS " . self::lockTableName . " ( " .
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
	
}
