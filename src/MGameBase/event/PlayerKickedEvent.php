<?php
namespace MGameBase\event;

use pocketmine\event\Cancellable;
use MGameBase\MGameBase;
class PlayerKickedEvent extends Event implements Cancellable{
	private $player;
	public static $handlerList;
	public function __construct(MGameBase $plugin, $player){
		parent::__construct($plugin);
		$this->player = $player;
	}
	
	public function getPlayer(){
		return $this->player;
	}
}