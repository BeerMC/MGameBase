<?php
namespace MGameBase\event;

use pocketmine\event\Cancellable;
use MGameBase\MGameBase;
use MGameBase\event\Event;
class AccountSetXpEvent extends Event implements Cancellable{
	private $player;
	private $exp;
	public static $handlerList;
	public function __construct(MGameBase $plugin, $player, $exp){
		parent::__construct($plugin);
		$this->player = $player;
		$this->expp = $exp;
	}
	
	public function getPlayer(){
		return $this->player;
	}
	
	public function getSettingXp(){
		return $this->expp;
	}
}