<?php
namespace MGameBase;

use pocketmine\network\protocol\TextPacket;
use pocketmine\event\player\PlayerTextPreSendEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Attribute;
use pocketmine\Player as P;

class Player extends P{
	
	public function MdataPacket(\pocketmine\network\protocol\DataPacket $packet, $needACK = false, $game=false){

			$identifier = $this->interface->putPacket($this, $packet, $needACK, false);

	}
	
	/**
	 * @param $value
	 */
	public function MsetAllowFlight($value){
		$this->allowFlight = (bool) $value;
		$this->sendSettings();
	}

	/**
	 * @return bool
	 */
	public function MgetAllowFlight(){
		return $this->allowFlight;
	}

	/**
	 * @param bool $value
	 */
	public function MsetFlying(bool $value){
		$this->flying = $value;
		$this->sendSettings();
	}

	/**
	 * @return bool
	 */
	public function MisFlying(){
		return $this->flying;
	}
	/*
	
	public function sendPopup($message, $subtitle = ""){
		$ev = new PlayerTextPreSendEvent($this, $message, PlayerTextPreSendEvent::POPUP);
		$this->server->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$pk = new TextPacket();
			$pk->type = TextPacket::TYPE_POPUP;
			$pk->source = $ev->getMessage();
			$pk->message = $subtitle;
			$this->dataPacket($pk);
			return true;
		}
		return false;
	}

	public function sendTip($message){
		$ev = new PlayerTextPreSendEvent($this, $message, PlayerTextPreSendEvent::TIP);
		$this->server->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$pk = new TextPacket();
			$pk->type = TextPacket::TYPE_TIP;
			$pk->message = $ev->getMessage();
			$this->dataPacket($pk);
			return true;
		}
		return false;
	}
	

	public function setMovementSpeed($amount){
		$this->movementSpeed = $amount;
		@$this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($amount);
	}


	public function getMovementSpeed(){
		return $this->movementSpeed;
	}
	*/

	
	/*public function getRoom($game){
		if(!isset($this->room[$game]) or $this->room[$game] == null){
			return null;
		}else{
			if(($plugin = $this->server->getPluginManager()->getPlugin($game)) !== null){
				return $plugin->getInstance()->getRoom($this->room[$game]);
			}else{
				return null;
			}
		}
	}
	
	public function setRoom($game, $roomid){
		$this->room[$game] = $roomid;
	}
	*/
	
	/*
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
	*/
}
?>