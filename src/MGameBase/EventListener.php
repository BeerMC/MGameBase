<?php
namespace MGameBase;

use pocketmine\event\Listener;

use pocketmine\entity\Attribute;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerCreationEvent;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryEvent;

use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;

use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;

use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\Player;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\Item;

use MGameBase\event\PlayerTextPreSendEvent;
use MGameBase\task\MessageDelayedTask;
use MGameBase\MGameBase;
use MGameBase\MiniMail;
use MGameBase\Friend;
class EventListener implements Listener{
	
	private $plugin;
	
	public function __construct(MGameBase $plugin){
		$this->plugin = $plugin;
	}
	
	/**
	 * @param PlayerCommandPreprocessEvent $event
	 *
	 * @priority MONITOR
	 */	
	public function onChat(PlayerCommandPreprocessEvent $event){
		try{
			if(!$this->plugin->isConnected()){
				return;
			}
			$player = $event->getPlayer();
			$name = $player->getName();
            $account = $this->plugin->getMP($player)->getAccount();
			$msg = $event->getMessage();
			if($this->plugin->isPlayerAuthenticated($player)){
				
			}else{
				if(!isset($this->plugin->level[spl_object_hash($player)])){
					return;
				}
				if($msg == "back"){
					$array = [1,2,3];
					if(in_array($this->plugin->level[spl_object_hash($player)],$array)){
			        $player->sendMessage("§l§a- §eBack to the last but one");
			        $player->sendMessage("§l§a- §e已返回上一个程序");
					$this->plugin->level[spl_object_hash($player)]--;
					}
					$event->setCancelled();
					return true;
				}
				switch($this->plugin->level[spl_object_hash($player)]){
					case 0:
					$event->setCancelled();
					$account = strtolower($msg);
					if($account != strtolower($player->getName())){
		                $player->sendMessage("- §c请正确输入你的账号: §3§9".$player->getName());
					}
		    		$valid = true;
		    		$len = strlen($account);
		    		if($len > 16 or $len < 3){
			        	$valid = false;
		                //$player->sendMessage("- §cThe account includes at least 3 characters and at most 16 characters");
		                $player->sendMessage("- §c账户至少为3个字符 至多为16个字符");
					    return true;
			    	}
			    	for($i = 0; $i < $len and $valid; ++$i){
			    		$c = ord($account{$i});
			    		if(($c >= ord("a") and $c <= ord("z")) or ($c >= ord("A") and $c <= ord("Z")) or ($c >= ord("0") and $c <= ord("9")) or $c === ord("_")){
			     			continue;
			    		}
			    		$valid = false;
			     		break;
			    	}
				    if(!$valid or in_array($account,MGameBase::$keywords)){
		             //$player->sendMessage("- §cThe account isn't allowed to include special characters or space or sensitive vocabulary");
		             $player->sendMessage("- §c账户不可以包括特殊符号与空格以及敏感词汇");
					return true;
				    }
					$player->sendMessage("§a搜索中§f...");
					if($this->plugin->isBanned($account)){
		            // $player->sendMessage("- §cThe account is banned");
		             $player->sendMessage("- §c此账户已被封禁");
					return true;
					}
					$this->plugin->getMP($player)->setAccount($account);
					if($this->plugin->isRegistered($account)){
						$this->plugin->getMP($player)->setLang($this->plugin->getPlayerLang($player, true));
						$player->sendMessage($this->plugin->getMessage($player,"auth.type.password"));
						$this->plugin->level[spl_object_hash($player)] = -1;
					}else{
						if($this->plugin->isLogined($account)){
		                //$player->sendMessage("- §cThis account has already joined");
		                $player->sendMessage("- §c此账户已在游戏中");
						return true;
						}
		                //$player->sendMessage("- §aThe account isn't registered§7:§6".$account);
		                //$player->sendMessage("- §bPlease type a password to register");
						$player->sendMessage("- §a您输入的账户未被注册§7:§6".$account);
		                $player->sendMessage("- §b请输入密码进行注册");
						$this->plugin->level[spl_object_hash($player)] = 1;
					}
					return true;
					
					case 1:
					$event->setCancelled();
					$password = &$msg;
					if(strlen($password) > 16 || strlen($password) < 3){
		                //$player->sendMessage("- §cThe password includes at least 3 characters and at most 16 characters");
		                $player->sendMessage("- §c密码至少为3个字符 至多为16个字符");
						return true;
					}
					$this->plugin->getMP($player)->setPassword($password);
					//$player->sendMessage("- §aYour Password§7:§6".$password);
				    //$player->sendMessage("- §aPlease type again to make sure");
				    $player->sendMessage("- §a你的密码§7:§6".$password);
				    $player->sendMessage("- §a请再次输入一遍进行确认");
				    $this->plugin->level[spl_object_hash($player)] = 2;
					return true;
					
					case 2:
					$event->setCancelled();
					$password = &$msg;
					if($password == $this->plugin->getMP($player)->getPassword()){
						$this->plugin->registerPlayer($player,$password);
						$player->sendMessage("§6Register successfully! 注册成功!");
			            //$player->sendMessage("- §2Please choose a language");
		                //$player->sendMessage("- §2en    ch    zh    jp    ru   ko");
			            $player->sendMessage("- §2请选择一种语言");
		                $player->sendMessage("- §2".implode(" | ",array_keys($this->plugin->language["message"]))."");
						$this->plugin->level[spl_object_hash($player)] = 3;
				 	}else{
						//$player->sendMessage("- §cYour Password§7:§6".$this->plugin->getMP($player)->getPassword()."§c.Please type again or 'back' to return");
						$player->sendMessage("- §c你的密码§7:§6".$this->plugin->getMP($player)->getPassword()."§c,请输入正确或输入back重新输入");
					}
					return true;
					
					case 3:
					$event->setCancelled();
					$lang = &$msg;
					if(!in_array($lang,MGameBase::$langlist)){
		            $player->sendMessage("- §2".implode(" | ",array_keys($this->plugin->language["message"]))."");
					return true;
					}
					$this->plugin->setPlayerLang($player,$lang);
					$this->plugin->authenticatePlayer($player);
			        $player->sendMessage($this->plugin->getMessage($player,"register.success"));
			        $this->plugin->level[spl_object_hash($player)] = "ok";
					return true;
					
					
					
					case -1:
					$event->setCancelled();
					$password = &$msg;
					$data = $this->plugin->getPlayerData($player);
					if(isset($data["password"]) and hash_equals($data["password"], $this->plugin->hash(strtolower($account), $password))){
					$this->plugin->authenticatePlayer($player);
			        $this->plugin->level[spl_object_hash($player)] = 3;
					}else{
						$player->sendMessage($this->plugin->getMessage($player,"login.error.password"));
					}
					return true;
					
					default:
					return true;
				}
			}			
		}catch(\Exception $e){
			return false;
		}

	}
	
	/**
	 * @param PlayerCreationEvent $event
	 *
	 * @priority MONITOR
	 */	
	public  function onCreate(PlayerCreationEvent $event){
		$interface = $event->getInterface();
		$cid = $event->getClientId();
		$this->plugin->addInterface($cid,$interface);
		$event->setPlayerClass(\MGameBase\Player::class);
	}
	

	/**
	 * @param PlayerQuitEvent $event
	 *
	 * @priority MONITOR
	 */
	public function onJoin(PlayerJoinEvent $e){
		try{
			$player = $e->getPlayer();
			if($player instanceof Player){
				if(!$this->plugin->isPlayerAuthenticated($player)){
					$this->plugin->deauthenticatePlayer($player);
				}
				$this->plugin->updatePlayer($player);
				$this->plugin->checkVIP($player);
				if($this->plugin->isVIP($player)){
					$this->plugin->getServer()->broadcastMessage(MGameBase::FORMAT."§6小游戏§c会员§6玩家§a".$player->getName()."§6进入了游戏");
					$this->plugin->sendInfo($player);
				}
			}			
		}catch(\Exception $e){
			return null;
		}
	}
	
	

	/**
	 * @param PlayerQuitEvent $event
	 *
	 * @priority MONITOR
	 */
	public function onQuit(PlayerQuitEvent $event){
		try{
			$player = $event->getPlayer();
			$this->plugin->removeInterface($player);
			unset($this->plugin->level[spl_object_hash($player)]);
			$this->plugin->setPlayerData($player,"playing",0);			
		}catch(\Exception $e){
			return null;
		}

	}

	public function onDataSend(DataPacketSendEvent $event){
		try{
			$pk = $event->getPacket();
			if($pk instanceof \pocketmine\network\protocol\TextPacket){
				$ev = new PlayerTextPreSendEvent($this->plugin, $event->getPlayer(),$pk->message,$pk->type);
				$this->plugin->getServer()->getPluginManager()->callEvent($ev);
				$pk->message = $ev->getMessage();
				$pk->type = $ev->getMessageType();
				if($ev->isCancelled()){
					$event->setCancelled();
				}
			}			
		}catch(\Exception $e){
			return null;
		}

	}

}
?>