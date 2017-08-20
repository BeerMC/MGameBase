<?php

namespace MGameBase\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use MGameBase\MGameBase;

class LangCommand extends Command{
	
	private $plugin;

	public function __construct(MGameBase $plugin){
		parent::__construct("lang", "description", "usage");

		$this->setPermission("MGameBase.command.lang");

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
		if($args == null){
			$sender->sendMessage("- §1/§6lang §f<§a".implode(" | ",array_keys($this->plugin->language["message"]))."§f>");
			return true;
		}
			if(count($args) == 1){
				if(in_array($args[0],MGameBase::$langlist)){
					if($this->plugin->setPlayerLang($sender,$args[0])){
						$sender->sendMessage($this->plugin->getMessage($sender,"lang.change"));
					}else{
						$sender->sendMessage($this->plugin->getMessage($sender,"lang.noexist"));
					}
				}else{
					$sender->sendMessage("- §1/§6lang §f<§a".implode(" §7|§a ",array_keys($this->plugin->language["message"]))."§f>");
				}
		    }else{
				$sender->sendMessage("- §1/§6lang §f<§a".implode(" §7|§a ",array_keys($this->plugin->language["message"]))."§f>");
		    }
			return true;
	}
}
?>