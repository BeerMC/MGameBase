<?php

namespace MGameBase\event;
use pocketmine\event\Cancellable;
use MGameBase\event\Event;
use MGameBase\MGameBase;
class PlayerTextPreSendEvent extends Event implements Cancellable{
	const MESSAGE = 0;
	const POPUP = 1;
	const TIP = 2;
	const TRANSLATED_MESSAGE = 3;
	const WHISPER = 4;
	
	public static $handlerList = null;
	protected $message;
	protected $type = self::MESSAGE;
	
	public function __construct(MGameBase $plugin, $player, $message, $type = self::MESSAGE){
		parent::__construct($plugin);
		$this->player = $player;
		$this->message = $message;
		$this->type = $type;
	}
	public function getMessage(){
		return $this->message;
	}
	public function setMessage($message){
		$this->message = $message;
	}
	public function getMessageType(){
		return $this->type;
	}
	public function getPlayer(){
		return $this->player;
	}	
}