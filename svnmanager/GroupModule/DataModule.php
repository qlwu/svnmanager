<?php
/*
 * Created on 17-Jan-2005
 *
 * DataModule of groups, where all actual changes into database and
 * input file regarding Groups are done.
 *
 */
using('System.Data');

require_once('svnmanager/global/Security.php');

class DataModule extends TModule
{		
	
	public function onLoad($param)
	{
		parent::onLoad($param);		

		require("config.php");
		
		//Set UTF-8 encoding
		mb_internal_encoding('UTF-8');
		
		$this->Database->setDataSourceName($dsn);
	}

	public function getUsername($id)
	{
		$user = $this->Database->Execute("SELECT * FROM users WHERE id=" . makeSqlString($id));
		if($user)
			return $user->fields['name'];
		else
			return null;
	}
	
	public function getUserId($name)
	{
		$id = $this->Database->Execute("SELECT * FROM users WHERE name=" . makeSqlString($name));
		if($id)
			return $id->fields['id'];
		else
			return null;
	}
	
	public function removeUser($id, $userid)
	{
		$s_id = makeSqlString($id);
		$s_userid = makeSqlString($userid);
		$exists = $this->Database->Execute("SELECT * FROM usersgroups WHERE userid=$s_userid AND groupid=$s_id");
		if($exists->recordCount()>0)
		{
			$result = $this->Database->Execute("DELETE FROM usersgroups WHERE userid=$s_userid AND groupid=$s_id");
			$this->rebuildAccessFile();
		}
		$exists->Close();				
	}
	
	public function addUser($id, $userid)
	{
		$s_id = makeSqlString($id);
		$s_userid = makeSqlString($userid);
		$exists = $this->Database->Execute("SELECT * FROM usersgroups WHERE userid=$s_userid AND groupid=$s_id");
		if($exists->recordCount()==0)
		{
			$result = $this->Database->Execute("INSERT INTO usersgroups (userid, groupid) VALUES ($s_userid, $s_id)");
			$result->Close();
			$this->rebuildAccessFile();
		}
		$exists->Close();		
		
	}
	
	public function isTaken($name)
	{
		$result = $this->Database->Execute("SELECT * FROM groups WHERE name=" . makeSqlString($name));
		return $result->RecordCount() > 0; 			
	}
	
	public function createGroup($name)
	{
		$userid = $this->User->getId();
		$s_name = makeSqlString($name);
		$s_userid = makeSqlString($userid);
		$result = $this->Database->Execute("INSERT INTO groups (id, name, adminid) VALUES (null, $s_name, $s_userid)");
		$groupid = $this->Database->Insert_ID();
		$result->Close();
		$this->rebuildAccessFile();		
		return $groupid;
	}
	
	public function deleteGroup($id)
	{
		//Delete access of group
		$s_id = makeSqlString($id);
		$result = $this->Database->Execute("DELETE FROM groupprivileges WHERE groupid=$s_id");
		$result->Close();
		$result = $this->Database->Execute("DELETE FROM groups WHERE id=$s_id");
		$result->Close();
		$this->rebuildAccessFile();
	}
	
	public function renameGroup($id, $newname)
	{
		$s_id = makeSqlString($id);
		$s_newname = makeSqlString($newname);
		$result = $this->Database->Execute("UPDATE groups SET name=$s_newname WHERE id=$s_id");
		$result->Close();
		$this->rebuildAccessFile();
	}
	
	public function changeGroupOwner($id, $newownerid)
	{
		$s_id = makeSqlString($id);
		$s_newownerid = makeSqlString($newownerid);
		$result = $this->Database->Execute("UPDATE groups SET adminid=$s_newownerid WHERE id=$s_id");
		$result->Close();
		$this->rebuildAccessFile();
	}
	
	public function areGroups()
	{
		$result = $this->Database->Execute("SELECT * FROM groups");
		return $result->recordCount()>0;
	}

	function rebuildAccessFile()
	{
		require_once("./svnmanager/library/class.accessfile.php");
		$accessfile = new AccessFile();
		$accessfile->createFromDatabase();
	}
 
} 
?>
