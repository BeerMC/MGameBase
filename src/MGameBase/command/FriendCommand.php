<?php

namespace MGameBase\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use MGameBase\MGameBase;
use MGameBase\Friend;
use MGameBase\MiniMail;
class FriendCommand extends Command{
	
	private $plugin;

	public function __construct(MGameBase $plugin){
		parent::__construct("friend", "description", "usage");

		$this->setPermission("MGameBase.command.friend");

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
		if(!$this->plugin->isConnected()){
			$sender->sendMessage($this->plugin->getMessage($sender,"not.connected"));
			return true;
		}
		if(!$this->plugin->isPlayerAuthenticated($sender)){
			$sender->sendMessage($this->plugin->getMessage($sender,"mail.unlogin"));
			return true;
		}
		$mp = MGameBase::getInstance()->getMP($sender);
				$cmd = array_shift($args);
	     		switch($cmd){
					case "添加":
					case "add":
					if(count($args) < 1){
						$sender->sendMessage("- §1/§6friend §a添加 §f<§a小游戏账号§f>§7或§f<§a:玩家名字§f>");
						return true;
					}
					$give = array_shift($args);
					$player = false;
					if($give{0} == ":" or $give{0} == "："){
						$give = str_replace($give,["：",":"],""); // 通过在线玩家加好友
						if(($player = $this->plugin->getServer()->getPlayerExact($give)) instanceof Player){
							if($this->plugin->isPlayerAuthenticated($player)){
								$give = $this->plugin->getMP($player)->getAccount();
							}else{
								$sender->sendMessage($this->plugin->getMessage($sender,"f.add.unlogin",[$give]));
								return true;
							}
						}else{
							$sender->sendMessage($this->plugin->getMessage($sender,"f.add.offline",[$give]));
							return true;
						}
					}else{
						//通过玛戈比账号加
					}
					if(strtolower($give) == strtolower($mp->getAccount())){
						$sender->sendMessage($this->plugin->getMessage($sender,"f.add.self"));
						return true;
					}
					if(!$this->plugin->isRegistered($give)){
						$sender->sendMessage($this->plugin->getMessage($sender,"f.add.noexist",[$give]));
						return true;
					}
					if($this->plugin->isFriend($mp->getAccount(),$give)){
						$sender->sendMessage($this->plugin->getMessage($sender,"f.add.already",[$give]));
						return true;
					}
					$this->plugin->addFriend($give, $mp->getAccount());
					$sender->sendMessage($this->plugin->getMessage($sender,"f.add.success",[$give]));
					if($player instanceof Player){
						$sender->sendMessage($this->plugin->getMessage($sender,"f.add.to",[$sender->getName()]));
					}
					return true;
					
					case "删除":
					case "del":
					case "delete":
					if(count($args) < 1){
						$sender->sendMessage("- §1/§6friend §a删除 §f<§a玛戈比账号§f>");
						return true;
					}
					$give = array_shift($args);
					if(!$this->plugin->delFriend($give, $mp->getAccount())){
						$sender->sendMessage($this->plugin->getMessage($sender,"f.del.noexit",[$give]));
						return true;
					}
					$sender->sendMessage($this->plugin->getMessage($sender,"f.del.success",[$give]));
					return true;
					
					case "列表":
					case "list":
					$list = $this->plugin->listFriend($mp->getAccount());
					if(isset($args[0])){
						$page = (int) $args[0];
					}else{
						$page = 1;
					}
					$list = array_reverse($list);
					$output = "";
					$max = ceil(count($list) / 7);
					$pro = 1;
					$page = (int)$page;
					$output .= $this->plugin->getMessage($sender,"f.list.page",[$page,$max])."\n";
					$current = 1;
					foreach($list as $friend){
					$cur = (int) ceil($current / 7);
					if($cur > $page) 
						continue;
					if($pro == 8) 
						break;
					if($page === $cur){
						$output .= "§b".$friend."§7;";
						$pro++;
					}
					$current++;
					}
					$sender->sendMessage($output);
					$sender->sendMessage($this->plugin->getRandMessage($sender,"f.list.rand"));
					return true;
					
					case "好友申请":
					case "request":
					case "requests":
					if(isset($args[0])){
						$page = (int) $args[0];
					}else{
						$page = 1;
					}
					$list = Friend::listrequests($sender);
					$list = array_reverse($list);
					$output = "";
					$max = ceil(count($list) / 7);
					$pro = 1;
					$page = (int)$page;
					$output .= $this->plugin->getMessage($sender,"f.request.page",[$page,$max])."\n";
					$current = 1;
					foreach($list as $data){
					$cur = (int) ceil($current / 7);
					if($cur > $page) 
						continue;
					if($pro == 8) 
						break;
					if($page === $cur){
					    $output .= "§f<ID:".$data["id"].">§b".$data["come"]."§7[".date("Y-m-d H:i:s",$data["time"])."]\n";
						$pro++;
					}
					$current++;
					}
					$sender->sendMessage($output);
					$sender->sendMessage($this->plugin->getRandMessage($sender,"f.request.rand"));
					return true;
					
					case "同意":
					case "accept":
					$id = array_shift($args);
					switch($id){
						case "全部":
						case "all":
						$ok = $this->plugin->acceptAllFriend($sender);
						if($ok !== false){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.accept.all.success",[$ok]));
							return true;
						}
						$sender->sendMessage($this->plugin->getMessage($sender, "f.accept.all.noexist"));
						return true;
						
						default:
						if(!is_numeric($id)){
							$sender->sendMessage("- §1/§6friend §a同意 §f<§aid§f>/§a全部");
							return true;
						}
						$read = Friend::read($id);
						if($read == null){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.accept.noexist",[$id]));
							return true;
						}
						if($read["give"] !== $mp->getAccount()){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.accept.notmine",[$id]));
							return true;
						}
						if($this->plugin->acceptFriend($id)){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.accept.success",[$id]));
						}else{
			    			$sender->sendMessage($this->plugin->getMessage($sender, "f.accept.noexist",[$id]));
						}
						return true;
				   	}
					
					case "拒绝":
					case "refuse":
					$id = array_shift($args);
					switch($id){
						case "全部":
						case "all":
						$ok = $this->plugin->refuseAllFriend($sender);
						if($ok !== false){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.refuse.all.success"));
							return true;
						}
						$sender->sendMessage($this->plugin->getMessage($sender, "f.refuse.all.noexist"));
						return true;
						
						default:
						if(!is_numeric($id)){
							$sender->sendMessage("- §1/§6friend §a拒绝 §f<§aid§f>/§a全部");
							return true;
						}
						$read = Friend::read($id);
						if($read == null){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.refuse.noexist",[$id]));
							return true;
						}
						if($read["give"] !== $mp->getAccount()){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.refuse.notmine",[$id]));
							return true;
						}
						if($this->plugin->refuseFriend($id)){
							$sender->sendMessage($this->plugin->getMessage($sender, "f.refuse.success",[$id]));
						}else{
			    			$sender->sendMessage($this->plugin->getMessage($sender, "f.refuse.noexist",[$id]));
						}
						return true;
					}
					
					case "查看":
					case "look":
					return true;
					
					default:
					$sender->sendMessage("- §1/§6friend §f<§a添加 | 删除 | 列表 | 好友申请 | 同意 | 拒绝 | 查看§f>");
					return true;
				}
	}
}
?>