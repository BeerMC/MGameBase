<?php

namespace MGameBase\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use MGameBase\MGameBase;

class ShowCommand extends Command{
	
	private $plugin;

	public function __construct(MGameBase $plugin){
		parent::__construct("show", "description", "usage");

		$this->setPermission("MGameBase.command.show");

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
		$this->plugin->showOtherPlayers($sender);
	}
}
?>