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
				" primary key(id), " .
				" unique key(fileid) " .
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

	public function installWOPIEndpoint() {
		if(file_exists($this->config->paths->templates . "wopi.php")) {
			$this->session->warning(_("wopi.php template file already exists. You need to manually copy wopi-template.php from the module directory to site/templates/wopi.php"));
		} else {
			$srcFile = $this->config->paths->LoolEditor . "wopi-template.php";
			$dstFile = $this->config->paths->templates . "wopi.php";
			if($this->config->debug)
				$this->session->message(sprintf(_("Copying template file from %s to %s"), $srcFile, $dstFile));

			$this->files->copy($srcFile, $dstFile);

			$this->session->message(_("Installed wopi.php template to site/templates"));
		}

		$fg = $this->fieldgroups->get('wopi');
		if(! $fg) {
			$fg = new Fieldgroup();
			$fg->name = 'wopi';
			$f = $this->fields->get('title');
			$fg->add($f);
			$fg->save();
			if($this->config->debug) $this->session->message(_("Installed fieldgroup for template wopi"));
		} else {
			if($this->config->debug) $this->session->message(_("Fieldgroup for template wopi already present"));
		}

		$t = $this->templates->get("wopi");
		if(! $t) {
			$t = new Template();
			$t->fieldgroup = $fg;
			$t->name = "wopi";
			$t->tags = "LOOL";
			$t->urlSegments = 1;
			$t->save();
			$this->session->message(_("Installed template wopi"));
		} else {
			if($this->config->debug)
				$this->session->message(_("Template 'wopi' already present"));
		}

		$p = $this->pages->get("/wopi/");
		if($p instanceOf NullPage) {
			$p = new Page();
			$p->template = $t;
			$p->parent = $this->pages->get('/');
			$p->name = "wopi";
			$p->title = "WOPI";
			$p->removeStatus(Page::statusUnpublished);
			$p->addStatus(Page::statusHidden);
			$p->addStatus(Page::statusLocked);
			$p->save();
			$this->session->message(_("Installed WOPI endpoint page /wopi/"));
		} else {
			if($this->config->debug)
				$this->session->message(_("WOPI endpoint already present"));
		}
	}

	public function removeWOPIEndpoint() {
		$p = $this->pages->get('/wopi/');
		if(! $p instanceOf NullPage) {
			$p->of(false);
			$p->removeStatus(Page::statusLocked);
			$p->delete();
			$this->session->message(_("Removed WOPI endpoint page"));
		}

		$t = $this->templates->get('wopi');
		if($t) {
			$this->templates->delete($t);
			$this->session->message(_("Removed wopi template. Delete site/templates/wopi.php manually"));
		}

		$fg = $this->fieldgroups->get('wopi');
		if($fg) {
			$this->fieldgroups->delete($fg);
		}
	}

}
