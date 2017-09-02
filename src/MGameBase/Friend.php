<?php
namespace MGameBase;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

use MGameBase\Friend;

class Friend{
	
	public $give;
	public $come;
	public $time;
	
	public function __construct($give,$come){
		$this->give = $give;
		$this->come = $come;
		$this->time = time();
	}
	
	public function send(){
		$give = $this->give;
		$come = $this->come;
		$time = $this->time;
		 MGameBase::getInstance()->getDB()->query("INSERT INTO friends
			(come, give, time)
			VALUES
			('$come','$give','$time')");
			return true;
	}
	
//=======================================================================\\
    public static function read($id){
	    $result = MGameBase::getInstance()->getDB()->query("SELECT * FROM friends WHERE id = '".$id."'");
		$data = $result->fetchArray(SQLITE3_ASSOC);
		if($data == false){
			return null;
		}
		if(isset($data["give"])){
			return $data; //array
		}
		return null;
	}
	
    public static function accept($id){
		$data = self::read($id);
		if($data !== null){
			$come = $data["come"];
			$give = $data["give"];
			MGameBase::getInstance()->getDB()->query("DELETE FROM friends WHERE id = '".$id."'");
			if(MGameBase::getInstance()->isFriend($come,$give)){
				return false;
			}
			if($data["come"] == $data["give"]){
				return false;
			}
			self::make($give,$come);
			return array("come"=>$come,"give"=>$give);
		}
		return false;
	}
	
    public static function acceptall($player){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
		$result =  MGameBase::getInstance()->getDB()->query("SELECT * FROM friends WHERE give = '".$account."'");
		$datas = array();
		while($row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$datas[] = $row;
		}
		$friends = MGameBase::getInstance()->listFriend($player);
		foreach($datas as $data){
			 MGameBase::getInstance()->getDB()->query("DELETE FROM friends WHERE id = '".$data["id"]."'");
			if(!in_array($data["give"],$friends) and !in_array($data["come"],$friends) and $data["come"] !== $data["give"]){
				self::make($data["give"],$data["come"]);
			}
		}
		return $datas;
	}
    public static function refuse($id){
		$data = self::read($id);
		if($data !== null){
			$come = $data["come"];
			$give = $data["give"];
			MGameBase::getInstance()->getDB()->query("DELETE FROM friends WHERE id = '".$id."'");
			if(MGameBase::getInstance()->isFriend($come,$give)){
				return false;
			}
			return array("come"=>$come,"give"=>$give);
		}
		return false;
	}
	
    public static function refuseall($player){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
		$result =  MGameBase::getInstance()->getDB()->query("SELECT id FROM friends WHERE give = '".$account."'");
		$data = array();
		while($row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$data[] = $row["id"];
		}
		foreach($data as $id){
			 MGameBase::getInstance()->getDB()->query("DELETE FROM friends WHERE id = '".$id."'");
		}
		return $datas;
	}
	
    public static function listrequests($player){
		if($player instanceof \pocketmine\Player){
		$account = MGameBase::getInstance()->getMP($player)->getAccount();
		}else{
		$account = $player;
		}
	    $result =  MGameBase::getInstance()->getDB()->query("SELECT * FROM friends WHERE give = '".$account."'");
		$data = array();
		while($row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$data[] = $row;
		}
		return $data;
	}
	
	public static function check($player){
		$how = count(self::listrequests($player));
		return $how;
	}
	
	public static function make($give,$come){
		$givedata = MGameBase::getInstance()->getPlayerData($give, "friend");
		if($givedata !== null){
			$friends = explode(";",$givedata["friend"]);
			$friends[] = $come;
			$str = implode(";",$friends);
			MGameBase::getInstance()->setPlayerData($give,"friend",$str);
			unset($friends,$givedata,$str);
		}
		$comedata = MGameBase::getInstance()->getPlayerData($come, "friend");
		if($comedata !== null){
			$friends = explode(";",$comedata["friend"]);
			$friends[] = $give;
			$str = implode(";",$friends);
			MGameBase::getInstance()->setPlayerData($come,"friend",$str);
			unset($friends,$comedata,$str);
		}
		MGameBase::getInstance()->sendMail($come,"system",MGameBase::getInstance()->getMessage($come,"f.make.success",$give));
		MGameBase::getInstance()->sendMail($give,"system",MGameBase::getInstance()->getMessage($give,"f.make.success",$come));
	}

}
?>