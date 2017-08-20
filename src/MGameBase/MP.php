<?php
namespace MGameBase;

use pocketmine\Server;

class MP{
	
	public function __construct($name){
		$this->name = $name;
		$this->room = [];
	}
	
	public $room;
	
	public function getRoom($game){
		if(!isset($this->room[$game])){
			return null;
		}else{
			if(($plugin = Server::getInstance()->getPluginManager()->getPlugin($game)) !== null){
				return $plugin->getInstance()->getRoom($this->room[$game]);
			}else{
				return null;
			}
		}
	}
	
	public function setRoom($game, $roomid){
		$this->room[$game] = $roomid;
	}
	
	public function isGaming(){
		foreach($this->room as $game => $roomid){
			if($roomid != null){
				return true;
			}
		}
		return false;
	}
	
	public function setAccount($account){
		$this->account = $account;
	}
	
	public function getAccount(){
		return $this->account;
	}
	
	public function setPassword($p){
		$this->password = $p;
	}
	
	public function getPassword(){
		return $this->password;
	}
	
	public function setGamename($n){
		$this->gamename = $n;
	}
	
	public function getGamename(){
		return $this->gamename;
	}
	
	public function setLv($lv){
		$this->lv = $lv;
	}
	
	public function getLv(){
		return $this->lv;
	}
	
	public function setXp($xp){
		$this->xp = $xp;
	}
	
	public function getXp(){
		return $this->xp;
	}
	
	public function setVIP($vip){
		$this->vip = $vip;
	}
	
	public function getVIP(){
		return $this->vip;
	}
	
	public function setVIPTime($viptime){
		$this->viptime = $viptime;
	}
	
	public function getVIPTime(){
		return $this->viptime;
	}	
	
	public function setCoin($coin){
		$this->coin = $coin;
	}
	
	public function getCoin(){
		return $this->coin;
	}
	
	public function setBeer($beer){
		$this->beer = $beer;
	}
	
	public function getBeer(){
		return $this->beer;
	}
	
	public function setLang($lang){
		$this->lang = $lang;
	}
	
	public function getLang(){
		return $this->lang;
	}
	
}
?>