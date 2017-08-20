<?php
namespace MGameBase\event;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;
use MGameBase\MGameBase;
use MGameBase\event\Event;
class AccountBannedEvent extends Event implements Cancellable{
	private $account;
	public static $handlerList;
	public function __construct(MGameBase $plugin, $account){
		parent::__construct($plugin);
		$this->account = $account;
	}
	
	public function getAccount(){
		return $this->account;
	}
}