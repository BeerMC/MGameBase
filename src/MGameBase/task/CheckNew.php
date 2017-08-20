<?php

namespace MGameBase\task;

use pocketmine\scheduler\PluginTask;
use MGameBase\MGameBase;
use MGameBase\MiniMail;
use MGameBase\Friend;

class CheckNew extends PluginTask{


	public function __construct(MGameBase $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	public function onRun($currentTick){
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if(!$this->plugin->getMP($player)->isGaming()){
				$check = MiniMail::check($player);
				if($check !== 0){
					$player->sendMessage($this->plugin->getMessage($player,"mail.check",$check));
				}
				$check = Friend::check($player);
				if($check !== 0){
					$player->sendMessage($this->plugin->getMessage($player,"f.check",$check));
				}				
			}
		}
	}
}
?>