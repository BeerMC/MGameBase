<?php

namespace MGameBase\task;

use pocketmine\scheduler\PluginTask;
use MGameBase\MGameBase;

class MessageDelayedTask extends PluginTask{
	public function __construct(MGameBase $plugin, $player){
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->player = $player;
	}

	public function onRun($currentTick){
		$this->plugin->sendCommandHelp($this->player);
	}
}
?>