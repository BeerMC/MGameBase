<?php
namespace MGameBase;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

use MGameBase\MGameBase;

class MiniMail{
	
	public $message;
	public $come;
	public $give;
	public $time;
	public $readed;
	
	public function __construct($give,$come,$message){
		if($give instanceof \pocketmine\Player){
			$mp = MGameBase::getInstance()->getMP($give);
			$give = $mp->getAccount();
		}
		if($come instanceof \pocketmine\Player){
			$mp = MGameBase::getInstance()->getMP($come);
			$come = $mp->getAccount();
		}
		$this->message = $message;
		$this->come = $come;
		$this->give = $give;
		$this->time = time();
		$this->readed = 0;  //0未读 1已读 2回收站内
	}
	
	public function send(){
		$message= $this->message;
		$come= $this->come;
		$give= $this->give;
		$time= $this->time;
		
		 MGameBase::getInstance()->getDB()->query("INSERT INTO messages
			(message, come, give, time, readed)
			VALUES
			('$message','$come','$give','$time',0)");
			return true;
	}
	
    public static function del($id){
		 MGameBase::getInstance()->getDB()->query("UPDATE messages SET  readed  = 2 WHERE id = '".$id."'");
		return true;
	}
	
    public static function restore($id){
		MGameBase::getInstance()->getDB()->query("UPDATE messages SET  readed  = 1 WHERE id = '".$id."'");
		return true;
	}
	
    public static function restoreall($player){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
		$result = MGameBase::getInstance()->getDB()->query("UPDATE messages SET  readed  = 1 WHERE give = '".$account."' and readed = 2");
		return true;
	}
	
    public static function read($id){
	    $result = MGameBase::getInstance()->getDB()->query("SELECT * FROM messages WHERE id = '".$id."'");
			$data = $result->fetchArray(\SQLITE3_ASSOC);
			if(isset($data["message"])){
			 MGameBase::getInstance()->getDB()->query("UPDATE messages SET  readed  = 1 WHERE id = '".$id."'");
			return $data; //array
			}
		return null;
	}
	
    public static function readall($player){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
		$result =  MGameBase::getInstance()->getDB()->query("SELECT * FROM messages WHERE give = '".$account."'");
		$data = array();
		while($row = $result->fetchArray(\SQLITE3_ASSOC))
		{
			$data[$row["id"]] = $row;
			MGameBase::getInstance()->getDB()->query("UPDATE messages SET  readed  = 1 WHERE id = '".$row["id"]."'");
		}
		return $data;
	}

    public static function delall($player){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
		$result =  MGameBase::getInstance()->getDB()->query("SELECT id FROM messages WHERE give = '".$account."'");
		$data = array();
		while($row = $result->fetchArray(\SQLITE3_ASSOC))
		{
			$data[] = $row["id"];
		}
		foreach($data as $id){
			 MGameBase::getInstance()->getDB()->query("UPDATE messages SET  readed  = 2 WHERE id = '".$id."'");
		}
		return true;
	}	
	
    public static function listdata($player,$par){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
	    $result =  MGameBase::getInstance()->getDB()->query("SELECT * FROM messages WHERE give = '".$account."'");
		$data = array();
		switch($par){
			case "new":
			case "新":
			while($row = $result->fetchArray(\SQLITE3_ASSOC))
			{
				if($row["readed"] == 0){
				$data[] = $row;
				}
			}
			return $data;
			
			case "old":
			case "旧":
			while($row = $result->fetchArray(\SQLITE3_ASSOC))
			{
				if($row["readed"] == 1){
				$data[] = $row;
				}
			}
			return $data;
			
			case "deleted":
			case "回收站":
			while($row = $result->fetchArray(\SQLITE3_ASSOC))
			{
				if($row["readed"] == 2){
				$data[] = $row;
				}
			}
			return $data;
			
			case "all":
			case "全部":
			while($row = $result->fetchArray(\SQLITE3_ASSOC))
			{
				$data[] = $row;
			}
			return $data;
			
			default:
			return $data;
		}
	}
	
	public static function check($player){
		$how = count(self::listdata($player,"new"));
		return $how;
	}
	
	public static function delete2(){
		//$now = time() - 604800;
		$now = time() - 5*24*3600;
		$result =  MGameBase::getInstance()->getDB()->query("DELETE FROM messages WHERE readed = 2 and time < '".$now."'");
	}
}
?>