<?php

namespace MGameBase\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use MGameBase\MGameBase;
use MGameBase\Friend;
use MGameBase\MiniMail;
class MgbCommand extends Command{
	
	private $plugin;

	public function __construct(MGameBase $plugin){
		parent::__construct("mgb", "description", "usage");

		$this->setPermission("MGameBase.command.mgb");

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
			$this->sendCommandHelp($sender);
			return true;
		}
			switch($args[0]){
				case "注册":
				case "登录":
				case "join":
				case "register":
				case "login":
				if(!$this->plugin->isConnected()){
					$sender->sendMessage($this->plugin->getMessage($sender,"not.connected"));
					return true;
				}
				if($this->plugin->isPlayerAuthenticated($sender)){
		            //$sender->sendMessage("- §cYou are already logined");
		            $sender->sendMessage("- §c你已经登录了,无需重复登陆");
					return true;
				}
				$mp = MGameBase::getInstance()->getMP($sender);
				$mp->setAccount("UnLogin");
				$this->plugin->level[spl_object_hash($sender)] = 0;
				$this->plugin->deauthenticatePlayer($sender);
				//$sender->sendMessage("§l- §3Please Type An Account You Like");
				$sender->sendMessage("- §3请输入账户:你的名字");
				return true;
				
				case "logout":
				if(!$this->plugin->isConnected()){
						$sender->sendMessage($this->plugin->getMessage($sender,"not.connected"));
					return true;
				}
				unset($this->plugin->level[spl_object_hash($sender)]);
				$this->plugin->deauthenticatePlayer($sender);
				$sender->sendMessage("§l- §3已退出§7<§b小游戏账号§7>");
				return true;
				
				case "rename":
				if(!$this->plugin->isConnected()){
						$sender->sendMessage($this->plugin->getMessage($sender,"not.connected"));
					return true;
				}
				if(!$this->plugin->isPlayerAuthenticated($sender)){
		            $sender->sendMessage($this->plugin->getMessage($sender,"not.login"));
					return true;
				}
				if(!isset($args[1]) or $args[1] == " "){
					$sender->sendMessage("- §1/§6mgb §arename §f名称");
					return true;
				}
				$gamename = "";
				foreach($args as $k => $v){
					if($k !== 0){
						$gamename .= $v." ";
					}
				}
				$gamename = trim($gamename);
				$gamename = mb_substr($gamename,0,7,'utf-8');
				if(in_array(strtolower($gamename),MGameBase::$keywords)){
					$sender->sendMessage($this->plugin->getMessage($sender,"rename.keyword",[$gamename]));
					return true;
				}
				$mp = MGameBase::getInstance()->getMP($sender);
				$mp->setGamename($gamename);
				$this->plugin->setPlayerData($sender, "gamename", $gamename);
				$sender->sendMessage($this->plugin->getMessage($sender,"rename.success",[$gamename]));
				return true;
				
				case "me":
				case "info":
				$this->plugin->sendInfo($sender);
				return true;
				
				case "money":
				if($this->plugin->config["游戏币兑换金钱开关"] != true or $this->plugin->config["游戏币兑换金钱汇率"] <= 0){
					$sender->sendMessage(MGameBase::FORMAT."§c当前服务器没有开启§a[游戏币兑换金钱功能]");
					return true;
				}
				if(!isset($args[1]) or !is_numeric($args[1]) or $args[1] <= 0){
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§emoney §6[要兑换的金钱量]");
					$sender->sendMessage(MGameBase::FORMAT."§b当前服务器§a[游戏币兑换金钱汇率]§b为 §f1 :".$this->plugin->config["游戏币兑换金钱汇率"]);
					return true;
				}
				$money = $args[1];
				if($money > $this->plugin->getCoin($sender) * $this->plugin->config["游戏币兑换金钱汇率"]){
					$sender->sendMessage(MGameBase::FORMAT."§c兑换§a".$money."§c金钱需要§6".$money / $this->plugin->config["游戏币兑换金钱汇率"]."游戏币, 你有:§b".$this->plugin->getCoin($sender)."游戏币");
					return true;
				}
				$plugin = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
				if($plugin != null){
					$plugin->addMoney($sender->getName(), $money);
					$this->plugin->updateCoin($sender, - $money / $this->plugin->config["游戏币兑换金钱汇率"]);
					$sender->sendMessage(MGameBase::FORMAT."§b兑换成功, 获得§a金钱".$money."§b, 花费§6".$money / $this->plugin->config["游戏币兑换金钱汇率"]."游戏币§b, 现在你有:§6".$this->plugin->getCoin($sender)."游戏币");
				}else{
					$sender->sendMessage(MGameBase::FORMAT."§c当前服务器没有安装§a[EconomyAPI]插件");
				}
				return true;
				
				case "coin":
				if(!$sender->isOp()){
					 $sender->sendMessage(MGameBase::FORMAT."你没有权限使用此命令");
					 return true;
				}
				if(!isset($args[3]) or !is_numeric($args[3])){
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§ecoin §r§2add §6[name] §a[amount]");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§ecoin §r§2del §6[name] §a[amount]");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§ecoin §r§2set §6[name] §a[amount]");
					return true;
				}
				switch($args[1]){
					case "add":
					$this->plugin->updateCoin($args[2], $args[3]);
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§a授权成功§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§6给予玩家 §b".$args[2]." §6游戏币 §c".$args[3]." §6个");
					return true;
					
					case "del":
					$this->plugin->updateCoin($args[2], -$args[3]);
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§a授权成功§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§6销毁玩家 §b".$args[2]." §6游戏币 §c".$args[3]." §6个");
					return true;
					
					case "set":
					$this->plugin->setCoin($args[2], $args[3]);
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§a授权成功§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§6设置玩家 §b".$args[2]." §6游戏币 §c".$args[3]." §6个");
					return true;
					
					default:
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§ecoin §r§2add §6[name] §a[amount]");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§ecoin §r§2del §6[name] §a[amount]");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§ecoin §r§2set §6[name] §a[amount]");
					return true;
				}
				return true;
				
				case "vip":
				if(!$sender->isOp()){
					$sender->sendMessage(MGameBase::FORMAT."§c您没有权限使用此命令");
					return true;
				}
				if(!isset($args[1]) or !isset($args[2])){
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§evip §r§2add §6[name] [time]");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§evip §r§2del §6[name]");
					return true;
				}
				switch($args[1]){
					case "add":
					if(!isset($args[3]) or !is_numeric($args[3])){
						$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
						$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§evip §r§2add §6[name] [time]");
						return true;
					}
					$time = intval(86400*$args[3]);
					$vip = $this->plugin->getVIP($args[2]) + 1;
					$this->plugin->setVIP($args[2], $vip, $time);
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§a授权成功§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§6已将玩家§2[".$args[2]."]§6设置为§cVIP§b (".$vip.")§6级,§a".$args[3]."§6天");
					$player = $this->plugin->getServer()->getPlayerExact($args[2]);
					if($player instanceof Player){
						if($vip <= 1){
						$player->sendMessage(MGameBase::FORMAT."§b[§cVIP§b]§r§6您成为了§2(".$vip.")§6级VIP玩家");
						}else{
						$player->sendMessage(MGameBase::FORMAT."§b[§5S§cVIP§b]§r§6您成为了§2(".$vip.")§6级VIP玩家");
						}
					}
					return true;
					
				case "del":
					$this->plugin->setVIP($args[2] , 0);
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§a授权成功§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§6已将玩家§2[".$args[2]."]§6夺去§cVIP§6权限");
					$player = $this->plugin->getServer()->getPlayerExact($args[2]);
					if($player instanceof Player){
					$player->sendMessage(MGameBase::FORMAT."§b[§cVIP§b]§r您不再是VIP玩家");
					}
					return true;
					break;
					
					default:
					$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§evip §r§2add §6[name]");
					$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§evip §r§2del §6[name]");
					return true;
					break;			
				}
				return true;
				
				case "bug":
					$games = [];
					foreach(MGameBase::$allgames as $plugin=>$name){
						$games[strtolower($plugin)] = $name;
					}
					if(isset($args[2]) and $args[2] !== " "){
						if(isset($games[strtolower($args[1])]) or in_array($args[1], $games)){
							if(in_array($args[1], $games)){
								foreach($games as $plugin => $name){
									if($name == $args[1]){
										$game = $plugin;
										continue;
									}
								}
							}else{
								$game = strtolower($args[1]);
							}
							$bug = "";
							foreach($args as $key => $val){
								if($key >= 2){
									$bug .= $val." ";
								}
							}
							if(!$this->plugin->isConnected()){
								$sender->sendMessage(MGameBase::FORMAT."§aOK");
								$this->plugin->uploadBug($sender->getName(), $this->plugin->config["服主的账号"], $game, $bug, false);
							}else{
								$this->plugin->uploadBug($sender->getName(), $this->plugin->config["服主的账号"], $game, $bug, true);
								$sender->sendMessage(MGameBase::FORMAT."§6上传成功,请等待小游戏作者§3Matt§6进行检查修复");
							}
						}else{
						$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
						$sender->sendMessage(MGameBase::FORMAT."§b输入正确的小游戏名");
						$sender->sendMessage(MGameBase::FORMAT."§a".implode("§6 | §a", $games));							
						}
					}else{
						$sender->sendMessage(MGameBase::FORMAT."§7|-------------§c格式错误§7-------------|");
						$sender->sendMessage(MGameBase::FORMAT."§b/§cmgb §l§3bug §r§2游戏名 §6发生的错误内容");
						$sender->sendMessage(MGameBase::FORMAT."§a".implode("§6 | §a", $games));
					}
					return true;
					break;						
				
				case "update":
				case "更新":
				case "更新插件":
				if(!$sender->isOp()){
					$sender->sendMessage(MGameBase::FORMAT."§c您没有权限使用此命令");
					return true;
				}
				$this->plugin->update();
				return true;
				default:
				$this->sendCommandHelp($sender);
				return true;
			}
	}
	
	public function sendCommandHelp($sender){
		$sender->sendMessage(MGameBase::FORMAT."§7查询我的信息§6/mgb §bme");
		$sender->sendMessage(MGameBase::FORMAT."§7向本作者建议§6/mgb §bbug");
		$sender->sendMessage(MGameBase::FORMAT."§7金币兑换金钱§6/mgb §bmoney");
		if($sender->isOp()){
			$sender->sendMessage(MGameBase::FORMAT."§7金币设置内容§6/mgb §bcoin");
			$sender->sendMessage(MGameBase::FORMAT."§7会员设置内容§6/mgb §bvip");
			$sender->sendMessage(MGameBase::FORMAT."§7更新插件§6/mgb §bupdate");
		}
		if($this->plugin->isMGB() and $this->plugin->isConnected()){
			$sender->sendMessage(MGameBase::FORMAT."§7登录小游戏账号§a/mgb §blogin");
			$sender->sendMessage(MGameBase::FORMAT."§7注销小游戏账号§a/mgb §blogout");
			$sender->sendMessage(MGameBase::FORMAT."§7更换小游戏内的名称§a/mgb §brename");
		}else{

		}
	}
}
?>