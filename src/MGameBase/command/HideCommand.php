<?php

namespace MGameBase\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use MGameBase\MGameBase;

class HideCommand extends Command{
	
	private $plugin;

	public function __construct(MGameBase $plugin){
		parent::__construct("hide", "description", "usage");

		$this->setPermission("MGameBase.command.hide");

		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, $label, array $args){
		if(!$this->plugin->isEnabled()) return false;
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage("You are not a player!");
			return true;
		}		
		$this->plugin->hideOtherPlayers($sender);
	}
}
?>