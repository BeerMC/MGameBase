<?php
namespace MGameBase\event;

use pocketmine\event\Cancellable;
use MGameBase\event\Event;
use MGameBase\MGameBase;

class PlayerLangChangeEvent extends Event implements Cancellable{
	private $player;
	private $lang;
	public static $handlerList;
	public function __construct(MGameBase $plugin, $player, $lang){
		parent::__construct($plugin);
		$this->player = $player;
		$this->lang = $lang;
	}
	
	public function getPlayer(){
		return $this->player;
	}
	
	public function getLang(){
		return $this->lang;
	}
}