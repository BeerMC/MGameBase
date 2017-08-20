<?php
namespace MGameBase\event;

use MGameBase\MGameBase;
use MGameBase\event\Event;
class PlayerAuthEvent extends Event{
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