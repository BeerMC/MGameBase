<?php

namespace MGameBase\task;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use MGameBase\MGameBase;

class CheckBugs extends PluginTask{
	
	public function __construct(MGameBase $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	public function onRun($currentTick){
		$config = new Config($this->plugin->path . "bugs.yml", Config::YAML, array());
		$bugs = $config->getAll();
		foreach($bugs as $key => $info){
			$sender = $info["sender"];
			$owner = $info["owner"];
			$game = $info["game"];
			$bug = $info["bug"];
			$time = $info["time"];
			if($info["sql"] == false){
				if($this->plugin->isConnected()){
					$this->plugin->db->query("INSERT INTO bugs
					(sender, owner, game, bug, time)
					VALUES
					('$sender', '$owner', '$game', '$bug', '$time')");
					$bugs[$key]["sql"] = true;
					$info["sql"] = true;
					if(($player = $this->plugin->getServer()->getPlayerExact($sender)) instanceof Player){
						$player->sendMessage(MGameBase::FORMAT."§3您的建议信已被§a报告服务器§3接收,等待处理~~~");
					}
				}
			}
			if($info["status"] == 0){
				if($this->plugin->isConnected()){
					$result = $this->plugin->db->query("SELECT status FROM bugs WHERE time = '".$time."' and sender = '".$sender."'");
						$data = $result->fetchArray(\SQLITE3_ASSOC);
						if($data["status"] == 1){
							$bugs[$key]["status"] = 1;
							$info["status"] = 1;
						}else{
						}
				}
			}
			if($info["status"] == 1){
				if(($player = $this->plugin->getServer()->getPlayerExact($sender)) instanceof Player){
					$player->sendMessage(MGameBase::FORMAT."§2您的建议信已被小游戏作者§3Matt§2处理§6完毕§2~~~赶紧截屏叫服主更新小游戏插件吧~~~");
					$bugs[$key]["status"] = 2;
					$info["status"] = 2;
				}
			}
		}
		$config->setAll($bugs);
		$config->save();
		
		$this->plugin->checkBottle();
	}

}
?>