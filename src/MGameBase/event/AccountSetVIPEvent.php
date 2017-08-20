<?php
namespace MGameBase\event;

use pocketmine\event\Cancellable;
use MGameBase\MGameBase;
use MGameBase\event\Event;
class AccountSetVIPEvent extends Event implements Cancellable{
	private $player;
	private $vip;
	public static $handlerList;
	public function __construct(MGameBase $plugin, $player, $vip){
		parent::__construct($plugin);
		$this->player = $player;
		$this->vip = $vip;
	}
	
	public function getPlayer(){
		return $this->player;
	}
	
	public function getSettingVIP(){
		return $this->vip;
	}
}