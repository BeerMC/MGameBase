<?php
namespace MGameBase\event;

use pocketmine\event\Cancellable;
use MGameBase\MGameBase;
use MGameBase\event\Event;
class AccountSetCoinEvent extends Event implements Cancellable{
	private $player;
	private $coin;
	public static $handlerList;
	public function __construct(MGameBase $plugin, $player, $coin){
		parent::__construct($plugin);
		$this->player = $player;
		$this->coin = $coin;
	}
	
	public function getPlayer(){
		return $this->player;
	}
	
	public function getSettingCoin(){
		return $this->coin;
	}
}