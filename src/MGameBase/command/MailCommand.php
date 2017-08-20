<?php

namespace MGameBase\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use MGameBase\MGameBase;
use MGameBase\Friend;
use MGameBase\MiniMail;
use MGameBase\Language;
class MailCommand extends Command{
	
	private $plugin;

	public function __construct(MGameBase $plugin){
		parent::__construct("mail", "description", "usage");

		$this->setPermission("MGameBase.command.mail");

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
					case "发送":
					case "send":
					if(count($args) < 2){
						$sender->sendMessage("- §1/§6mail §a发送 §f<§a小游戏账号§f> <§a消息§f>");
						return true;
					}
					$account = array_shift($args);
					$message = implode($args, " ");
					if(strtolower($account) == strtolower($mp->getAccount())){
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.send.self"));
						return true;
					}					
					if($this->plugin->isRegistered($account)){
					$this->plugin->sendMail($account,$mp->getAccount(),$message);
					$sender->sendMessage($this->plugin->getMessage($sender,"mail.send.success",[$account]));
					}else{
					$sender->sendMessage($this->plugin->getMessage($sender,"mail.send.noexist",[$account]));
					}
					return true;
					
					case "读取":
					case "read":
					$id = array_shift($args);
					if(strtolower($id) == "all"){
						$messages = MiniMail::readall($sender);
						foreach($messages as $id=>$data){
							$sender->sendMessage("§f<ID:".$messages[$id]["id"].">"."§1[§d".$this->plugin->getGamenameByAccount($messages[$id]["come"])."§7(".$messages[$id]["come"].")§1]§7:§a".$messages[$id]["message"]);
						}
						return true;
					}
					if(!is_numeric($id)){
						$sender->sendMessage("- §1/§6mail §a读取 §f<§aid§f>");
						return true;
					}
					$id = (int)$id;
					$data = MiniMail::read($id);
					if($data == null){
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.read.noexist",[$id]));
					}else{
						$sender->sendMessage("§f<ID:".$data["id"].">"."§1[§d".$this->plugin->getGamenameByAccount($data["come"])."§7(".$data["come"].")§1]§7:§a".$data["message"]);
					}
					return true;
					
					case "列表":
					case "list":
					$par = array_shift($args);
					if($par == null){
						$sender->sendMessage("- §1/§6mail §a列表 §f<§a新 | 旧 | 回收站 | 全部§f>");
						return true;
					}
					if(isset($args[0])){
						$page = (int) $args[0];
					}else{
						$page = 1;
						$sender->sendMessage("- §1/§6mail §a列表 §f<§a新 | 旧 | 回收站 | 全部§f> <§a页码§f>");
					}
					$list = MiniMail::listdata($sender,$par);
					$list = array_reverse($list);
					$output = "";
					$max = ceil(count($list) / 5);
					$pro = 1;
					$page = (int)$page;
					$output .= $this->plugin->getMessage($sender,"mail.list.page",[$page,$max])."\n";
					$current = 1;
					foreach($list as $data){
					$cur = (int) ceil($current / 5);
					if($cur > $page) 
						continue;
					if($pro == 6) 
						break;
					if($page === $cur){
					    $output .= "§f<ID:".$data["id"].">§d".$this->plugin->getGamenameByAccount($data["come"])."§7:§a".mb_strcut($data["message"],0,10,'utf-8')."......§7[".date("Y-m-d H:i:s",$data["time"])."]\n";
						$pro++;
					}
					$current++;
					}
					$sender->sendMessage($output);
					$sender->sendMessage($this->plugin->getRandMessage($sender,"mail.list.rand"));
					return true;
					
					case "删除":
					case "del":
					case "delete":
					$id = array_shift($args);
					if(!is_numeric($id)){
			        	if($id !== "all" and $id !== "全部"){
			     			$sender->sendMessage("- §1/§6mail §a删除 §f<§aid§f>/§a全部");
			     			return true;
				    	}
						MiniMail::delall($sender);
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.delall.success"));
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.del.save7"));
					}else{
					$read = MiniMail::read($id);
					if($read !== null){
						if($read["give"] == $mp->getAccount()){
		     			MiniMail::del($id);
			    		$sender->sendMessage($this->plugin->getMessage($sender,"mail.del.success",[$id]));
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.del.save7"));
						}else{
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.del.notmine",[$id]));
						}
					}else{
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.del.noexist",[$id]));
					}
					}
					return true;
					
					case "回收":
					case "restore":
					$id = array_shift($args);
					if(!is_numeric($id)){
			        	if($id !== "all" and $id !== "全部"){
			     			$sender->sendMessage("- §1/§6mail §a回收 §f<§aid§f>/§a全部");
			     			return true;
				    	}
						MiniMail::restoreall($sender);
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.restoreall.success"));
					}else{
					$read = MiniMail::read($id);
					if($read !== null){
						if($read["give"] == $mp->getAccount()){
		     			MiniMail::restore($id);
			    		$sender->sendMessage($this->plugin->getMessage($sender,"mail.restore.success",[$id]));
						}else{
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.restore.notmine",[$id]));
						}
					}else{
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.restore.noexist",[$id]));
					}
					}
					return true;
					
					case "悄悄话":
					case "secret":
					if(count($args) < 2){
						$sender->sendMessage("- §1/§6mail §a悄悄话 §f<§a玛戈比账号§f> <§a消息§f>");
						return true;
					}
					$account = array_shift($args);
					$message = implode($args, " ");
					if(strtolower($account) == strtolower($mp->getAccount())){
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.secret.self"));
						return true;
					}					
					if($this->plugin->isRegistered($account)){
						$this->plugin->sendMail($account,"-secret-",$message);
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.secret.success",[$account]));
					}else{
						$sender->sendMessage($this->plugin->getMessage($sender,"mail.secret.noexist",[$account]));
					}
					return true;
					
					case "漂流瓶":
					case "bottle":
					if(count($args) < 1){
						$sender->sendMessage("- §1/§6mail §a漂流瓶 <§a小纸条§f>");
						return true;
					}
					$message = implode($args, " ");
					$this->plugin->sendMail("-bottle-",$mp->getAccount(),$message);
					$sender->sendMessage($this->plugin->getMessage($sender,"mail.bottle.success",[$message]));
					return true;
					
					default:
					$sender->sendMessage("- §1/§6mail §f<§a发送 | 读取 | 列表 | 删除 | 回收 | 漂流瓶 | 悄悄话§f>");
					return true;
			}
	}
}
?>