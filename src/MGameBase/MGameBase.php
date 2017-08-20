<?php
namespace MGameBase;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\inventory\PlayerInventory;

use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\sound\DoorSound;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\PopSound;

use pocketmine\block\Chest;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest as TileChest;
use pocketmine\level\format\mcregion\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\tile\Sign;

use pocketmine\scheduler\PluginTask;

use pocketmine\network\Network;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\TileEntityDataPacket;
use pocketmine\network\protocol\AdventureSettingsPacket;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;

use MGameBase\EventListener;
use MGameBase\event\PlayerRegisterEvent;
use MGameBase\event\PlayerAuthEvent;
use MGameBase\event\AccountBannedEvent;
use MGameBase\event\PlayerKickedEvent;
use MGameBase\event\AccountSetVIPEvent;
use MGameBase\event\AccountSetCoinEvent;
use MGameBase\event\PlayerLangChangeEvent;
use MGameBase\event\AccountSetXpEvent;
use MGameBase\event\AccountUpdateXpEvent;

use MGameBase\task\CheckBugs;
use MGameBase\task\CheckNew;
use MGameBase\MiniMail;
use MGameBase\Language;
use MGameBase\Friend;

class MGameBase extends PluginBase implements Listener, CommandExecutor{
	
	const FORMAT = "§b#§7|§3小游戏核心§7|§b#§o ";
	
	public $players = [];
	
	protected $listener;
	
    public static $instance;
	
    public $path;
	
	public $level;
	
	protected $needAuth = [];
	
	public $isBeerMC = false;
	
	public static $levelexp = [
	0 => 1,
	1 => 1,
	2 => 160,
	3 => 550,
	4 => 1395,
	5 => 2920,
	6 => 6666,
	];	

	public $db;
		
	public static $allgames = [
	"MSkyWars" => "空岛战争",
	"MBattleBridge" => "战桥",
	"MPvpArean" => "个人竞技",
	];
	
	public $config;
	
	public static $string = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k","l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z","0", "1", "2", "3", "4", "5", "6", "7", "8", "9","_");	
	
	public static $keywords = array("add","all","alter", 
"analyze","and","as", 
"asc","asensitive","before", 
"between","bigint","binary", 
"blob","both","by", 
"call","cascade","case", 
"change","char","character", 
"check","collate","column", 
"condition","connection","constraint", 
"continue","convert","create", 
"cross","current_date","current_time", 
"current_timestamp","current_user","cursor", 
"database","databases","day_hour", 
"day_microsecond","day_minute","day_second", 
"dec","decimal","declare", 
"default","delayed","delete", 
"desc","describe","deterministic", 
"distinct","distinctrow","div", 
"double","drop","dual", 
"each","else","elseif", 
"enclosed","escaped","exists", 
"exit","explain","false", 
"fetch","float","float4", 
"float8","for","force", 
"foreign","from","fulltext", 
"goto","grant","group", 
"having","high_priority","hour_microsecond", 
"hour_minute","hour_second","if", 
"ignore","in","index", 
"infile","inner","inout", 
"insensitive","insert","int", 
"int1","int2","int3", 
"int4","int8","integer", 
"interval","into","is", 
"iterate","join","key", 
"keys","kill","label", 
"leading","leave","left", 
"like","limit","linear", 
"lines","load","localtime", 
"localtimestamp","lock","long", 
"longblob","longtext","loop", 
"low_priority","match","mediumblob", 
"mediumint","mediumtext","middleint", 
"minute_microsecond","minute_second","mod", 
"modifies","natural","not", 
"no_write_to_binlog","null","numeric", 
"on","optimize","option", 
"optionally","or","order", 
"out","outer","outfile", 
"precision","primary","procedure", 
"purge","raid0","range", 
"read","reads","real", 
"references","regexp","release", 
"rename","repeat","replace", 
"require","restrict","return", 
"revoke","right","rlike", 
"schema","schemas","second_microsecond", 
"select","sensitive","separator", 
"set","show","smallint", 
"spatial","specific","sql", 
"sqlexception","sqlstate","sqlwarning", 
"sql_big_result","sql_calc_found_rows","sql_small_result", 
"ssl","starting","straight_join", 
"table","terminated","then", 
"tinyblob","tinyint","tinytext", 
"to","trailing","trigger", 
"true","undo","union", 
"unique","unlock","unsigned", 
"update","usage","use", 
"using","utc_date","utc_time", 
"utc_timestamp","values","varbinary", 
"varchar","varcharacter","varying",
"when","where","while", 
"with","write","x509", 
"xor","year_month","zerofill", "unlogin","rcon","console","server","player","owner","fuck","back",);	
	
	public static function getInstance(){
		return self::$instance;
	}
	
	public function isNewAPI(){
		$reflector = new \ReflectionClass('\pocketmine\tile\Tile');
		$parameters = $reflector->getMethod('createTile')->getParameters();
		if(is_object($parameters[1])){
			if(stripos($parameters[1]->name, "level") !== false or $parameters[1] instanceof Level){
				return true;
			}
		}
		return false;
	}	
	
	public function getDB(){
		return $this->db;
	}	
	
	public function getDataBase(){
		return $this->db;
	}
	
	public function isMGB(){
		return true;
	}
	
	public function isConnected(){
		if($this->db == false){
			return false;
		}
		return true;
	}
	
public function check(){
		return;
		$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
		$this->getServer()->broadcastMessage(self::FORMAT."§b开始检测更新！");
		try
		{
			$name='MGameBase';
			$ver=file_get_contents("http://cdn.diqiucloud.com:88/plugins/Matt/${name}/ver.txt");
			if(version_compare($ver,$this->getDescription()->getVersion())<=0){
				$this->getServer()->broadcastMessage("§e这个插件为最新版本！");
				$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
			}
			else
			{
				$自动更新=$this->config["自动更新"];
				if($自动更新=="true")
				{
					$this->getServer()->broadcastMessage(self::FORMAT."§4开始自动下载最新版插件……");
					$this->update();
				}
				else
				{
					$this->getServer()->broadcastMessage(self::FORMAT."§4插件有新版本！");
					$this->getServer()->broadcastMessage("§5请输入[/mgb update]来更新插件！");
				}
				$this->getServer()->broadcastMessage("更新日志：".file_get_contents("http://cdn.diqiucloud.com:88/plugins/Matt/${name}/log.txt"));
				$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
			}
		}
		catch(\Exception $e)
		{
			$this->getServer()->broadcastMessage(self::FORMAT."§4[插件自动检查更新]失败！请联系作者！");
			$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
		}
	}

	 public function update()
	{
		return;
		$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
		$this->getServer()->broadcastMessage(self::FORMAT."§5插件开始自动更新插件！");
		$name='MGameBase';
		$ver=file_get_contents("http://cdn.diqiucloud.com:88/plugins/Matt/${name}/ver.txt");
		try
		{
			if(version_compare($ver,$this->getDescription()->getVersion())<=0)
			{
				$this->getServer()->broadcastMessage(self::FORMAT."§3插件已经是最新版了！");
				$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
			}
			else
			{
				$rawname=null;
				if(substr(PHP_VERSION,0,3)=='5.6')
				{
					$rawname='[5.6]'.$name.'_v'.$ver;
				}
				if(substr(PHP_VERSION,0,3)=='7.0')
				{
					$rawname='[7.0]'.$name.'_v'.$ver;
				}
				if($rawname==null)
				{
					$this->getServer()->broadcastMessage("§3您的PHP版本出现错误！");
					$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
					return true;
				}
				$rawdata=file_get_contents("http://cdn.diqiucloud.com:88/plugins/Matt/${name}/${rawname}.phar");
				$cwd=$this->getserver()->getpluginPath().basename(dirname(dirname(dirname(__FILE__))));
				$old = umask(0);
				chmod($cwd,0777);
				umask($old);
				//$srcdata=file_get_contents($cwd);
				file_put_contents($cwd,$rawdata);
				$this->getServer()->broadcastMessage(self::FORMAT."§3插件自动更新完毕！§d将在3秒后重启服务器！§e[§1$rawname"."§e]");
				$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
				sleep(3.5);
				$this->getserver()->shutdown();
			}
		}
		catch(\Exception $e)
		{
			$this->getServer()->broadcastMessage(self::FORMAT."§3[自动更新插件]失败！请联系作者！");
			$this->getServer()->broadcastMessage("§e▃▃▃▃▃▃▃▃▃▃▃▃▃");
		}
	}
	
	public function onload(){
		self::$instance = $this;
	}
	
	public $newapi;
	
	public function onEnable(){
		$this->getLogger()->info("§7欢迎使用正版§b".self::FORMAT."§7管理插件");
		$this->getLogger()->info("§d开始初始化...");
		if($this->isNewAPI()){
			$this->newapi = true;
		}else{
			$this->newapi = false;
		}
		$this->path = $this->getDataFolder();####
		$this->games = [];####
		$this->interfaces = [];
		$this->listener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($this->listener, $this);
		
		@mkdir($this->path . "games/", 0777, true);
		$this->config = (new Config($this->path . "config.yml", Config::YAML, array()))->getAll();####
		(new Config($this->path . "bugs.yml", Config::YAML, array()))->save();
		$this->language = (new Config($this->path . "language.yml", Config::YAML))->getAll();
		$this->loadLanguage();
		//$this->vips = (new Config($this->path . "vips.yml", Config::YAML, array()))->getAll();####
		//$this->coins = (new Config($this->path . "coins.yml", Config::YAML, array()))->getAll();####
		$this->loadConfig();
			$this->getLogger()->info('进行处理数据库');
			$this->db = new SQLiteDB($this->path.'data.db');
			if(!$this->db->success()){
				$this->getLogger()->info("§c数据库创建失败！！！");
				$this->db = false;
			}else{
				$this->getLogger()->info("数据库处理成功！！！");
			}
			if($this->db == false){
				$this->getLogger()->warning('数据库处理异常,数据处理模式关闭');
			}else{
				$this->getLogger()->info('§a数据库处理成功');
				$this->db->create(
				"players", 
				[
				"id" => "INTEGER PRIMARY KEY",
				"account" => "VARCHAR(16)",
				"password" => "TEXT",
				"gamename" => "CHAR(100)",
				"last_ip" => "VARCHAR(50)",
				"log_date" => "INTEGER DEFAULT 0",
				"reg_date" => "INTEGERC DEFAULT 0",
				"lang" => "TEXT DEFAULT ch",
				"email" => "TEXT",
				"exp" => "INTEGER DEFAULT 0",
				"coin" => "INTEGER DEFAULT 0",
				"beer" => "INTEGER DEFAULT 0",
				"vip" => "INTEGER DEFAULT 0",
				"viptime" => "INTEGER DEFAULT 0",
				"friend" => "TEXT",
				"permission" => "INTEGER DEFAULT 0",
				"playing" => "INTEGER DEFAULT 0",
				"status" => "INTEGER DEFAULT 0",
				"viptime" => "INTEGER DEFAULT 0",
				],
				null);
				$this->db->create(
				"messages", 
				[
				"id" => "INTEGER PRIMARY KEY",
				"come" => "VARCHAR(16)",
				"give" => "VARCHAR(16)",
				"time" => "INTEGER",
				"message" => "TEXT",
				"readed" => "INTEGER DEFAULT 0",
				],
				null);
				$this->db->create(
				"friends", 
				[
				"id" => "INTEGER PRIMARY KEY",
				"come" => "VARCHAR(16)",
				"give" => "VARCHAR(16)",
				"time" => "INTEGER",
				],
				null);
				$this->db->create(
				"bugs", 
				[
				"id" => "INTEGER PRIMARY KEY",
				"sender" => "VARCHAR(16)",
				"owner" => "VARCHAR(16)",
				"game" => "INTEGER",
				"bug" => "TEXT",
				"time" => "INTEGER",
				"status" => "INTEGER DEFAULT 0",
				],
				null);
				@mkdir($this->path . "players/", 0777, true);
				$this->getServer()->getScheduler()->scheduleRepeatingTask(new CheckBugs($this), 1800*20);
				$this->getServer()->getScheduler()->scheduleRepeatingTask(new CheckNew($this), 300*20);  //检查每个玩家是否有新消息 5min
				}
				$this->level = [];
	//-----------------------------------------------------
		$this->getLogger()->info("§d开始检测小游戏...");
		$this->games = $this->checkGames();####
		if(count($this->games) < 1){
			$this->getLogger()->info("§b没有检测到任何M系列小游戏,本插件进入独立模式");
		}else{
			foreach($this->games as $game){
				$game = $game->getGameName();
				@mkdir($this->path . "games/".$game."/", 0777, true);
				@mkdir($this->path . "games/".$game."/worlds/", 0777, true);
				@mkdir($this->path . "games/".$game."/players/", 0777, true);
				if($this->isConnected()){
				$this->db->create(
				$game, 
				[
				"id" => "INTEGER PRIMARY KEY",
				"account" => "VARCHAR(16)",
				"Win" => "INTEGER DEFAULT 0",
				"Lose" => "INTEGER DEFAULT 0",
				"Kill" => "INTEGER DEFAULT 0",
				"Death" => "INTEGER DEFAULT 0",
				"Time" => "INTEGER DEFAULT 0",
				],
				null);				
				}
				$this->getLogger()->info("§b已检测到M系列小游戏§c:§".mt_rand(1,7).self::$allgames[$game]);
			}
			$this->getLogger()->info("§b已将所有小游戏数据初始化");
		}
		$this->registerCommands();
		if($this->getServer()->getName() == "BeerMC"){
			$this->isBeerMC = true;
		}else{
			$this->isBeerMC = false;
		}
		$this->check();
	}
	
    public function loadConfig(){
    	$configs = [
    	"注意" => '选项的格式:true为打开,false为关闭,加入其他字符的后果自负',
    	"服主的账号" => "",
    	"非OP的游戏管理员" => [""],
		"游戏币兑换金钱开关" => false,
		"游戏币兑换金钱汇率" => 250,
    	];
    	foreach ($configs as $key => $value) {
    		if (isset($this->config[$key]) == false){
				$this->config[$key] = $value;
				if(is_array($value)){
					$value = implode(",", $value);
				}
				$this->getServer()->getLogger()->info("§7[初始化]已将§a".$key."§7设置为§b".$value);
    		}
    	}
    	$this->saveConfig();
    }
	
    public function saveConfig(){
    	$config = new Config($this->path . "config.yml",Config::YAML);
    	if(isset($this->config) == false){
    		$config->setAll(array());
    	}elseif(is_array($this->config) == false){
			$config->setAll(array());
    	}else{
    		$config->setAll($this->config);
    	}
    	$config->save(true);
    }
	
    public function loadLanguage(){
    	$configs = \MGameBase\Language::$message;
    	foreach ($configs as $key => $value) {
    		if(!isset($this->language["message"][$key])){
				$this->language["message"][$key] = $value;
    		}
    	}
    	$configs = \MGameBase\Language::$random;
    	foreach ($configs as $key => $value) {
    		if(!isset($this->language["random"][$key])){
				$this->language["random"][$key] = $value;
    		}
    	}
    	$this->saveLanguage();
    }

    public function saveLanguage(){
    	$config = new Config($this->path . "language.yml",Config::YAML);
    	if(isset($this->language) == false or is_array($this->language) == false){
    		$config->setAll(array());
    	}else{
    		$config->setAll($this->language);
    	}
    	$config->save();
    }
	
	
	public function onDisable(){
		$this->saveConfig();
		//$vip = new Config($this->path . "vips.yml", Config::YAML, array());
		//$vip->setAll($this->vips);
		//$vip->save();
		//$coin = new Config($this->path . "coins.yml", Config::YAML, array());
		//$coin->setAll($this->coins);
		//$coin->save();
		$this->getLogger()->info("§b已将所有数据保存");
	}
	
	public function isBeerMC(){
		return $this->isBeerMC;
	}	
	
	public function getMP($player){
		try{
			if($player instanceof Player){
				$player = strtolower($player->getName());
			}
			$player = strtolower($player);
			return isset($this->players[$player]) ? $this->players[$player] : null;			
		}catch(\Exception $e){
			return null;
		}

	}
	
	public function getInterface($player){
		if($player instanceof Player){
			$player = $player->getClientId();
		}
		if(isset($this->interfaces[$player])){
			return $this->interfaces[$player];
		}else{
			return null;
		}
	}
	
	public function addInterface($cid,$interface){
		$this->interfaces[$cid] = $interface;
	}
	
	public function removeInterface($player){
		if($player instanceof Player){
			$player = $player->getClientId();
		}
		if(isset($this->interfaces[$player])){
			unset($this->interfaces[$player]);
			return true;
		}else{
			return false;
		}
	}
	
	public function setFlying($player,$arg){
		$pk = new AdventureSettingsPacket();
		$pk->flags = 0;
		$pk->isFlying = $arg;
		$player->dataPacket($pk);
	}
	
	public function setAllowFlight($player,$arg){
		$pk = new AdventureSettingsPacket();
		$pk->flags = 0;
		$pk->allowFlight = $arg;
		$player->dataPacket($pk);
	}
	
	public function getAllowFlight($player){
		try{
			if(!method_exists(Player::class, 'getAllowFlight')){
				return false;
			}
			return $player->getAllowFlight();
		}catch(\Exception $e){
			echo "1";
		}
	}
	
	public function dataPacket($player,$pk){
		if($player instanceof Player){
			if(($inter = $this->getInterface($player)) != null){
				$inter->putPacket($player, $pk, $false, false);
			}			
		}
	}
	
	public function newPlayerData($account,$password,$gamename){
		try{
			if(!$this->isConnected()){
				return null;
			}
			if($account instanceof Player){
				$account = $this->getMP($account)->getAccount();
			}elseif($account instanceof MP){
				$account = $account->getAccount();
			}else{
				$account = $account;
			}
			$account = strtolower($account);
			$this->db->query("INSERT INTO players
			(account, password, gamename)
			VALUES
			('$account', '$password', '$gamename')");
			foreach(self::$allgames as $game => $chinese){
				$this->db->query("INSERT INTO ".$game."
				(account)
				VALUES
				('$account')");
			}
			return true;			
		}catch(\Exception $e){
			return false;
		}

	}
	

	public function getPlayerData($player, $k="*"){
		try{
			if(!$this->isConnected()){
				return null;
			}
			if($player instanceof Player){
				$account = $this->getMP($player)->getAccount();
			}elseif($player instanceof MP){
				$account = $player->getAccount();
			}else{
				$account = $player;
			}
			$account = strtolower($account);
			$result = $this->db->query("SELECT ".$k." FROM players WHERE account = '".$account."'");
			$data = $result->fetchArray(SQLITE3_ASSOC);
			if($data == false){
				return null;
			}
			return $data;
		}catch(\Exception $e){
			return null;
		}

	}
	
	public function setPlayerData($player, $k, $v){
		try{
			if(!$this->isConnected()){
				return null;
			}
			if($player instanceof Player){
				$account = $this->getMP($player)->getAccount();
			}elseif($player instanceof MP){
				$account = $player->getAccount();
			}else{
				$account = $player;
			}
			$account = strtolower($account);
			if($this->getPlayerData($account) !== null){
			$this->db->query("UPDATE players SET  ".$k."  = '".$v."' WHERE account = '".$account."'");
			return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function getPlayerGameData($player, $game, $k="*"){
		try{
			if(!$this->isConnected()){
				return null;
			}
			if($player instanceof Player){
				$account = $this->getMP($player)->getAccount();
			}elseif($player instanceof MP){
				$account = $player->getAccount();
			}else{
				$account = $player;
			}
			$account = strtolower($account);
			$result = $this->db->query("SELECT ".$k." FROM ".$game." WHERE account = '".$account."'");
			$data = $result->fetchArray(SQLITE3_ASSOC);
			if($data == false){
				return null;
			}
			return $data;
		}catch(\Exception $e){
			return null;
		}

	}
	
	public function setPlayerGameData($player, $game, $k, $v){
		try{
			if(!$this->isConnected()){
				return false;
			}
			if($player instanceof Player){
				$account = $this->getMP($player)->getAccount();
			}elseif($player instanceof MP){
				$account = $player->getAccount();
			}else{
				$account = $player;
			}
			$account = strtolower($account);
			if($this->getPlayerData($account) !== null){
			$this->db->query("UPDATE ".$game." SET  ".$k."  = '".$v."' WHERE account = '".$account."'");
			return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function getGamenameByAccount($account){
		try{
			if(!$this->isConnected()){
				return null;
			}
			if($account instanceof Player){
				$account = $this->getMP($account)->getAccount();
			}elseif($account instanceof MP){
				$account = $account->getAccount();
			}else{
				$account = $account;
			}
			$account = strtolower($account);
			$result = $this->db->query("SELECT gamename FROM players WHERE account = '$account'");
			$data = $result->fetchArray(SQLITE3_ASSOC);
			if($data == false){
				return null;
			}
			return $data["gamename"];
		}catch(\Exception $e){
			return null;
		}

	}
	
	public function isLogined($account){
		try{
			$data = $this->getPlayerData($account, "playing");
			if($data !== null){
				if($data["playing"] !== 0){
					return true;
				}
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}

	public function isRegistered($account){
		try{
			if(!$this->isConnected()){
				return false;
			}
			if($account instanceof Player){
			$account = $this->getMP($account)->getAccount();
			}else{
			}
			$result = $this->db->query("SELECT account FROM players WHERE account = '$account'");
			$data = $result->fetchArray(SQLITE3_ASSOC);
			if($data == false){
				return null;
			}
			if(isset($data["account"])){
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}

	public function isPlayerAuthenticated(Player $player){
		try{
			if(isset($this->needAuth[spl_object_hash($player)])){
				return false;
			}else{
				return true;
			}			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function registerPlayer(Player $player, $password){
		try{
			$account = $this->getMP($player)->getAccount();
			if(!$this->isRegistered($account)){
				$this->newPlayerData($account, $this->hash(strtolower($account), $password),$player->getName());
				$this->setPlayerData($player,'last_ip',$player->getAddress());
				$this->setPlayerData($player,'log_date',time());
				$this->setPlayerData($player,'reg_date',time());
				$event = new PlayerRegisterEvent($this, $player);
				$this->getServer()->getPluginManager()->callEvent($event);
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function unregisterPlayer(Player $player){
		try{
			$account = trim(strtolower($this->getMP($player)->getAccount()));
			$this->db("DELETE FROM players WHERE account = '" . $this->db->escape_string($account)."'");			
		}catch(\Exception $e){
			return false;
		}

	}	
	
	public function hash($salt, $password){
		return bin2hex(hash("sha512", $password . $salt, true) ^ hash("whirlpool", $salt . $password, true));
	}
	
	public function authenticatePlayer(Player $player){
		try{
			if(isset($this->needAuth[spl_object_hash($player)])){
				//$attachment = $this->needAuth[spl_object_hash($player)];
				//$player->removeAttachment($attachment);
				unset($this->needAuth[spl_object_hash($player)]);
			}
			$player->sendMessage($this->getMessage($player,"login.success"));
			$this->setPlayerData($player,'last_ip',$player->getAddress());
			$this->setPlayerData($player,'log_date',time());
			$event = new PlayerAuthEvent($this, $player);
			$this->getServer()->getPluginManager()->callEvent($event);
			$this->updatePlayer($player);
			$check = MiniMail::check($player);
			if($check !== 0){
				$player->sendMessage($this->getMessage($player,"mail.check",$check));
			}
			$check = Friend::check($player);
			if($check !== 0){
				$player->sendMessage($this->getMessage($player,"f.check",$check));
			}
			return true;			
		}catch(\Exception $e){
			return false;
		}

	}

	public function deauthenticatePlayer(Player $player){
		try{
			$this->needAuth[spl_object_hash($player)] = $player;
			$this->players[strtolower($player->getName())] = new \MGameBase\MP(strtolower($player->getName()));
			$this->players[strtolower($player->getName())]->setAccount("UnLogin");
			$this->updatePlayer($player);
			return true;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function updatePlayer(Player $player){
		try{
			$mp = $this->getMP($player);
			if($mp == null){
				$this->deauthenticatePlayer($player);
				$mp = $this->getMP($player);
			}
			if(!$this->isConnected()){
				$mp->setAccount("UnLogin");
				$mp->setXp(0);
				$mp->setLv(0);
				$mp->setVIP($this->getVIP($player));
				$mp->setVIPTime($this->getVIPTime($player));
				$mp->setLang("ch");
				$mp->setGamename($player->getName());
				$mp->setCoin($this->getCoin($player));
				$mp->setBeer(0);
				return;
			}
			if($mp->getAccount() == "UnLogin"){
				$mp->setXp(0);
				$mp->setLv(0);
				$mp->setVIP($this->getVIP($player));
				$mp->setVIPTime($this->getVIPTime($player));
				$mp->setLang("ch");
				$mp->setGamename($player->getName());
				$mp->setCoin($this->getCoin($player));
				$mp->setBeer(0);
				return;
			}
			$exp = $this->getXp($player);
			$mp->setXp($exp);
			$mp->setLv($this->ExpToLevel($exp));
			$mp->setVIP($this->getVIP($player));
			$mp->setVIPTime($this->getVIPTime($player));
			$mp->setGamename($this->getGamenameByAccount($player));
			$mp->setCoin($this->getCoin($player));
			$mp->setBeer(0);			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function getXp($player){
		try{
			$data = $this->getPlayerData($player, "exp");
			if($data !== null){
				return $data["exp"];
			}
			return 0;			
		}catch(\Exception $e){
			return 0;
		}
	}
	
	public function setXp($player, $exp){
		try{
			$event = new AccountSetXpEvent($this, $player, $exp);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($event->isCancelled()){
				return false;
			}
			if($this->setPlayerData($player,"exp",$exp)){
				if($player instanceof Player){
				$mp = $this->getMP($player);
				$mp->setXp($exp);
				$lv1 = $mp->getLv();
				$mp->setLv($this->ExpToLevel($exp));
				$lv2 = $mp->getLv();
				if($lv1 < $lv2){
					$this->getServer()->getScheduler()->scheduleDelayedTask(new ParticleCircleTask($this, $player, 5), 20);
				}
				}
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function updateXp($player, $exp){
		try{
			$event = new AccountUpdateXpEvent($this, $player, $exp);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($event->isCancelled()){
				return false;
			}
			$data = $this->getPlayerData($player, "exp");
			if($data !== null){
				$exp = $data["exp"] + $exp;
				if($exp < 0){
					$exp = 0;
				}
				unset ($data);
				if($player instanceof Player and $exp > 0){
					$level = $player->getLevel();
					$pos = $player->getPosition();
					for($i=0;$i<$exp;$i++){
					$level->spawnXPOrb($pos->add(mt_rand(-3,3)/10, mt_rand(-3,3)/10, mt_rand(-3,3)/10));
					}
					$sound = new \pocketmine\level\sound\SpellSound($pos);
					$level->addSound($sound);
				}
				return $this->setXp($player,$exp);
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function updateExp($player, $exp){
		return $this->updateXp($player, $exp);
	}
	
	public function ExpToLevel($exp){
		for($level = 1;$level < 6;$level++){
			if($exp >= self::$levelexp[$level]){
				return $level;
			}
		}
		return 0;
	}
	
	public function getNextLevelExp($level){
		if($level < 6){
			return self::$levelexp[$level+1];
		}else{
			return 0;
		}
	}
	
	public function ExpToAttribute(Player $player){
		try{
			$mp = $this->getMP($player);
			$level = $mp->getLv();
			$exp = $mp->getXp();
			$levelattribute = $player->getAttributeMap()->getAttribute(Attribute::EXPERIENCE_LEVEL);
			$expattribute = $player->getAttributeMap()->getAttribute(Attribute::EXPERIENCE);
			$levelattribute->setValue($level);
			$expattribute->setValue($exp/$this->getNextLevelExp($level));
			$pk = new UpdateAttributesPacket();
			$pk->entityId = 0;
			$pk->minValue = $expattribute->getMinValue();
			$pk->maxValue = $expattribute->getMaxValue();
			$pk->name = $expattribute->getName();
			$pk->value = $expattribute->getValue();
			$pk->encode();
			$player->dataPacket($pk);
			$pk = new UpdateAttributesPacket();
			$pk->entityId = 0;
			$pk->minValue = $levelattribute->getMinValue();
			$pk->maxValue = $levelattribute->getMaxValue();
			$pk->name = $levelattribute->getName();
			$pk->value = $levelattribute->getValue();
			$pk->encode();
			$player->dataPacket($pk);			
		}catch(\Exception $e){
			return false;
		}

	}

	public function isBanned($account){
		$data = $this->getPlayerData($account, "status");
		if($data !== null){
			if($data["status"] == 1){
				return true;
			}
		}
		return false;
	}
	
	public function ban($account){
		try{
			$event = new AccountBannedEvent($this, $account);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($event->isCancelled()){
				return false;
			}
			$this->setPlayerData($account, "status", 1);
			$this->kick($account);
			return true;			
		}catch(\Exception $e){
			return false;
		}

	}

	public function unban($account){
		try{
			$this->setPlayerData($account, "status", 0);
			return true;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function kick($account){
		try{
			foreach($this->getServer()->getOnlinePlayers() as $p){
				if($this->getMP($p)->getAccount() == $account){
					$event = new PlayerKickedEvent($this, $p);
					$this->getServer()->getPluginManager()->callEvent($event);
					if($event->isCancelled()){
						return false;
					}
					$p->kick();
					return true;
				}
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function isVIP($player){
		try{
			$data = $this->getPlayerData($player, "vip");
			if($data !== null){
				if($data["vip"] > 0){
					return true;
				}
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}
		/*
		try{
			if($player instanceof Player){
				$player = $player->getName();
			}
			if(isset($this->vips[strtolower($player)]) and $this->vips[strtolower($player)] > 0){
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}
*/
	}
	
	public function isSVIP($player){
		try{
			$data = $this->getPlayerData($player, "vip");
			if($data !== null){
				if($data["vip"] > 1){
					return true;
				}
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}
		/*
		try{
			if($player instanceof Player){
				$player = $player->getName();
			}
			if(isset($this->vips[strtolower($player)]) and $this->vips[strtolower($player)] >= 2){
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}
		*/
		//}
	}

	public function setVIP($player,$vip, $time = 0){
		try{
			$now = time();
			if($this->setPlayerData($player,"vip",$vip)){
				if($player instanceof Player){
					$mp = $this->getMP($player);
					$mp->setVIP($coin);
				}
			}
			if($this->setPlayerData($player,"viptime",$time+$now)){
				if($player instanceof Player){
					$mp = $this->getMP($player);
					$mp->setVIPTime($time+$now);
				}
			}
			return false;
		}catch(\Exception $e){
			return false;
		}
		/* 
		try{
			$now = time();
			if($player instanceof Player){
				$player = $player->getName();
			}
			$player = strtolower($player);
			$this->vips[$player] = ["vip"=>$vip,"time"=>$time+$now];
			$this->getMP($player)->setVIP($vip);
			$this->getMP($player)->setVIPTime($time+$now);
			$config = new Config($this->path . "vips.yml", Config::YAML, array());
			$config->set($player, ["vip"=>$vip,"time"=>$time+$now]);
			$config->save();
			return true;					
		}catch(\Exception $e){
			return false;
		}
		*/
	}
	
	public function getVIP($player){
		try{
			$data = $this->getPlayerData($player, "vip");
			if($data !== null){
				return $data["vip"];
			}
			return 0;			
		}catch(\Exception $e){
			return 0;
		}
		/*
		try{
			if($player instanceof Player){
				$player = $player->getName();
			}
			$player = strtolower($player);
			if(isset($this->vips[$player]["vip"])){
				return $this->vips[$player]["vip"];
			}else{
				return 0;
			}			
		}catch(\Exception $e){
			return 0;
		}
		*/
	}	

	public function getVIPTime($player){
		try{
			$data = $this->getPlayerData($player, "viptime");
			if($data !== null){
				return $data["viptime"];
			}
			return 0;			
		}catch(\Exception $e){
			return 0;
		}
		/*
		try{
			if($player instanceof Player){
				$player = $player->getName();
			}
			$player = strtolower($player);
			if(isset($this->vips[$player]["time"])){
				return $this->vips[$player]["time"];
			}else{
				return 0;
			}			
		}catch(\Exception $e){
			return 0;
		}
		*/
	}
	
//==============Coin==============\\
	public function getCoin($player){
		try{
			$data = $this->getPlayerData($player, "coin");
			if($data !== null){
				return $data["coin"];
			}
			return 0;			
		}catch(\Exception $e){
			return 0;
		}
		/*
		try{
			if($player instanceof Player){
				$player = $player->getName();
			}
			$player = strtolower($player);
			if(isset($this->coins[$player])){
				return $this->coins[$player];
			}else{
				$this->coins[$player] = 0;
				return 0;
			}			
		}catch(\Exception $e){
			return 0;
		}
			*/
		//}
	}

	public function setCoin($player,$coin){
		try{
			if($this->setPlayerData($player,"coin",$coin)){
				if($player instanceof Player){
					$mp = $this->getMP($player);
					$mp->setCoin($coin);
				}
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}
		/*if($this->isConnected()){
		$event = new AccountSetCoinEvent($this, $player, $coin);
		$this->getServer()->getPluginManager()->callEvent($event);
		if($event->isCancelled()){
			return false;
		}
		if($this->setPlayerData($player,"coin",$coin)){
			$this->getMP($player)->setCoin($coin);
			return true;
		}
		return false;			
		}else{
			
			try{
				if($player instanceof Player){
					$player = $player->getName();
				}
				if($coin < 0){
					$coin = 0;
				}
				$player = strtolower($player);
				$this->coins[$player] = $coin;
				$this->getMP($player)->setCoin($coin);
				$config = new Config($this->path . "coins.yml", Config::YAML, array());
				$config->set($player, $coin);
				$config->save(true);
				return true;				
			}catch(\Exception $e){
			return false;
			}	
			*/
		//}
	}
	
	public function updateCoin($player,$coin){
		try{
			$data = $this->getPlayerData($player, "coin");
			if($data !== null){
				$coin = $data["coin"] + $coin;
				if($coin < 0){
					$coin = 0;
				}
				unset ($data);
				return $this->setXp($player,$coin);
			}
			return false;
		}catch(\Exception $e){
			return false;
		}
			
			/*
			try{
				if($player instanceof Player){
					$player = $player->getName();
				}
				if(is_numeric($coin)){
					return $this->setCoin($player, $this->getCoin($player) + $coin);
				}else{
					return false;
				}				
			}catch(\Exception $e){
			return false;
		}
		*/
		//}
	}


	public function updateNameTag(Player $player){
		try{
			if($this->isPlayerAuthenticated($player)){
				$player->setNameTag("§3[§aLv§f.§6".$this->getMP($player)->getLv()."§3]§d".$this->getMP($player)->getGamename()."\n"."§a* §8".$this->getMP($player)->getAccount()."§f");
			}else{
				$player->setNameTag("§d".$player->getName());
			}			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function updateDisplayName(Player $player){
		if($this->isPlayerAuthenticated($player)){
			$player->setDisplayName($this->getMP($player)->getGamename());
		}else{
			//$player->setDisplayName("UnLogin");
		}
	}

//==============SHOW==============\\
	public function hidePlayer(Player $player) {
		$effect = Effect::getEffect(Effect::INVISIBILITY);
		$effect->setDuration(20 * 10000);
        $effect->setVisible(false);
		$player->addEffect($effect);
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			if ($p !== $player) {
				$p->hidePlayer($player);
			}
		}
	}

	public function showPlayer(Player $player) {
		$player->removeEffect(Effect::INVISIBILITY);  
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			if ($p !== $player) {
				$p->showPlayer($player);
			}
		}
	}
	
	public function hideOtherPlayers(Player $player) {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			if ($p !== $player) {
				$player->hidePlayer($p);
			}
		}
		$player->sendMessage(self::getMessage($player,"hide"));
	}

	public function showOtherPlayers(Player $player) {
		foreach ($this->getServer()->getOnlinePlayers() as $p) {
			if ($p !== $player) {
				$player->showPlayer($p);
			}
		}
		$player->sendMessage(self::getMessage($player,"show"));
	}
	
//==============Lang==============\\
    public function getDefaultLang(){
        return $this->defaultlang;
    }

	public function getPlayerLang($player, $force = false){
		try{
			if($force == false and $player instanceof Player){
				return $this->getMP($player)->getLang();
			}else{
				$data = $this->getPlayerData($player, "lang");
				if($data !== null){
					if(in_array($data["lang"], array_keys($this->language["message"])));
				}
				return "ch";
				}			
		}catch(\Exception $e){
			return "ch";
		}

	}
	
	public function setPlayerLang(Player $player, $lang){
		try{
			if(in_array($lang, array_keys($this->language["message"]))){
				$event = new PlayerLangChangeEvent($this, $player, $lang);
				$this->getServer()->getPluginManager()->callEvent($event);
				if($event->isCancelled()){
					return false;
				}
				$this->getMP($player)->setLang($lang);
				$this->setPlayerData($player,"lang",$lang);
				return true;					
			}
			return false;
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function setMessage($string, $msg, $lang = "ch"){
		try{
			if(!in_array($lang, $this->langlist)){
				$this->getServer()->getLogger()->warning('在使用 '.__METHOD__.' 时发生错误!不存在['.$lang.']语言');
				return false;
			}
			if(!isset($this->language["message"][$lang][$string])){
				$this->language["message"][$lang][$string] = $msg;
				$this->getServer()->getLogger()->info(TextFormat::GREEN.'成功向'.$lang.'语言中添加代码'.$string.'为'.$msg);
				return true;
			}else{
				$this->getServer()->getLogger()->warning('在使用 '.__METHOD__.' 时发生错误!在语言'.$lang.'中已存在['.$string.']代码');
				return false;
			}			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function getMessage($player, $string, $vals = null){
		try{
			$lang = $this->getPlayerLang($player);
			if(isset($this->language["message"][$lang][$string])){
				$msg = $this->language["message"][$lang][$string];
				if(!is_array($vals)){
					$val = $vals;
					$msg = str_replace("%0",$val,$msg);
					return $msg;
				}
				$count = 0;
				foreach($vals as $val){
					$msg = str_replace("%".$count,$val,$msg);
					$count++;
				}
				return $msg;
			}elseif(isset($this->language["message"]["ch"][$string])){
				$msg = $this->language["message"]["ch"][$string];
				if(!is_array($vals)){
					$val = $vals;
					$msg = str_replace("%0",$val,$msg);
					return $msg;
				}
				$count = 0;
				foreach($vals as $val){
					$msg = str_replace("%".$count,$val,$msg);
					$count++;
				}
				return $msg;
			}
			else{
			return $string;
			}			
		}catch(\Exception $e){
			return $string;
		}

	}
	
	public function getRandMessage($player, $string){
		try{
			$lang = $this->getPlayerLang($player);
			$len = strlen($string);
			$all = array();
			foreach($this->language["random"][$lang] as $k => $v){
				if(substr($k,0,$len) == $string){
					$all[] = $v;
				}
			}
			if(count($all) < 1){
				return $string;
			}
			return $all[mt_rand(0, count($all) - 1)];			
		}catch(\Exception $e){
			return $string;
		}

	}

//==============Communicate==============\\

/*
注: 
    下面代码中的"give"相当于"to",被接受者
    下面代码中的"come"相当于"from",发送者
	之所以这样是因为MySQL不支持to与from等字符

*/	
	public function sendMail($to,$from,$message){
		try{
		$obj = new MiniMail($to,$from,$message);
		$obj->send();
		return true;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function checkBottle(){
		try{
		$list = MiniMail::listdata("-bottle-","new");
		$amount = count($list);
		if($amount > 0){
			$bottle = MiniMail::read($list[mt_rand(0,$amount - 1)]["id"]);
			unset($list);
			foreach($this->getServer()->getOnlinePlayers() as $player){
				if($this->isPlayerAuthenticated($player)){
					$this->sendMail($player,"-bottle-",$bottle["message"]);
					$player->sendMessage($this->getMessage($player, "mail.bottle.to"));
					continue;
				}
			}
		}			
		}catch(\Exception $e){
			return false;
		}

	}
	
	
	public function isFriend($give,$come){
		try{
		$data = $this->getPlayerData($come, "friend");
		if($data !== null){
			$friends = explode(";",$data["friend"]);
			if(in_array($give,$friends)){
				return true;
			}
		}
		return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function acceptAllFriend($player){
		try{
		$datas = Friend::acceptall($player);
		if(count($datas) < 1){
			return false;
		}
		foreach($datas as $data){
			$this->sendMail($data["come"],"system",$this->getMessage($data["come"],"f.accept.to",$data["give"]));
		}
		return count($datas);			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function refuseAllFriend($player){
	try{
		$datas = Friend::refuseall($player);
		if(count($datas) < 1){
			return false;
		}
		foreach($datas as $data){
			$this->sendMail($data["come"],"system",$this->getMessage($data["come"],"f.refuse.to",$data["give"]));
		}
		return count($datas);		
	}catch(\Exception $e){
			return false;
		}

	}	
	
	public function acceptFriend($id){
		try{
			$data = Friend::accept($id);
			if($data !== false){
				$this->sendMail($data["come"],"system",$this->getMessage($data["come"],"f.accept.to",$data["give"]));
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function refuseFriend($id){
		try{
			$data = Friend::refuse($id);
			if($data !== false){
				$this->sendMail($data["come"],"system",$this->getMessage($data["come"],"f.refuse.to",$data["give"]));
				return true;
			}
			return false;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function addFriend($to,$from){  //指令无需判断其他
	try{
		$obj = new Friend($to,$from);
		$obj->send();
		$this->sendMail($to,"system",$this->getMessage($to,"f.add.to",$from));		
	}catch(\Exception $e){
			return false;
		}

	}
	
	public function delFriend($to,$from){  //指令直接if($this->delFriend)
		try{
		if(!$this->isFriend($to,$from)){
			return false;
		}
		$data = $this->getPlayerData($from, "friend");
		if($data !== null){
			$friends = explode(";",$data["friend"]);
			$founded = array_search($to, $friends);
        	if($founded !== false){
	    		array_splice($friends, $founded, 1);
				$str = implode(";",$friends);
				$this->setPlayerData($from,"friend",$str);
	         }
		}
		unset($data);
		$data = $this->getPlayerData($to);
		if($data !== null){
			$friends = explode(";",$data["friend"]);
			$founded = array_search($from, $friends);
        	if($founded !== false){
	    		array_splice($friends, $founded, 1);
				$str = implode(";",$friends);
				$this->setPlayerData($to,"friend",$str);
	         }
		}
		$this->sendMail($to,"system",$this->getMessage($to,"f.del.to",$from));
		return true;			
		}catch(\Exception $e){
			return false;
		}

	}
	
	public function listFriend($player){    //指令无需判断其他
	try{
		$data = $this->getPlayerData($player, "friend");
		$friends = array();
		if($data !== null){
			$friends = explode(";",$data["friend"]);
		}
		return $friends;		
	}catch(\Exception $e){
			return false;
		}

	}
	
	public function lookFriend($to,$from){
		
	}

	public function configExists($player, $game){
		if($player instanceof Player){
			$player = $player->getName();
		}
        return file_exists($this->path . "games/".$game."/players/" . strtolower($player) . ".yml");
    }
	
    public function getPlayerConfig($player, $game){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$array = array(
                "Name" => $player."",
				"Win" => 0,
				"Lose" => 0,
				"Kill" => 0,
				"Death" => 0,
				"Nametag" => 0,
				"Items" => array(),
				"Add" => array(),
				"Time" => 0,
				"Quit" => 0,
				"Gamemode" => 0,
				"Allowflight" => false,
				);
        if(!(file_exists($this->path . "games/".$game."/players/" . $player . ".yml")))
        {
            return new Config($this->path . "games/".$game."/players/" . $player . ".yml", Config::YAML, $array);
        }else{
            $config = new Config($this->path . "games/".$game."/players/" . $player . ".yml", Config::YAML, array());
			$all = $config->getAll();
			foreach($array as $key => $val){
				if(!isset($all[$key])){
					$all[$key] = $val;
				}
			}
			$config->setAll($all);
			$config->save();
			return $config;
		}
    }
	
	public function checkVIP($player){
		$viptime = $this->getVIPTime($player);
		if($viptime != 0){
			if($viptime < time()){
				$this->setPlayerData($player,"vip",0);
				$this->setPlayerData($player,"viptime",0);
			}
		}
	}
	
	public function checkGames(){
		$gs = [];
		foreach(self::$allgames as $k => $v){
			if(($game = $this->getServer()->getPluginManager()->getPlugin($k)) !== null){
				$gs[] = $game;
			}
		}
		return $gs;
	}
	
	public function copyLevel($levelname, $gamename){
        $p = dirname($this->getServer()->getDefaultLevel()->getFolderName());
        $gamew =  $p. "/plugins/MGameBase/games/".$gamename."/worlds/";
        $w = $p. "/worlds/";
		$files = scandir($w);
        foreach($files as $f) {
			if($f == $levelname){
                if ($f !== "." && $f !== ".." && is_dir($w.$f)) {
                    $this->recurse_copy($w . '/' . $f,$gamew . '/' . $f);
                }
			}
	  	}
	}
	
    public function recurse_copy($src,$dst){
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

	public function uploadBug($sender, $owner, $game, $bug, $sql=false){
		if(in_array(strtolower($sender), self::$keywords)){
			return false;
		}
		foreach(self::$allgames as $plugin=>$name){
			if(strtolower($plugin) == strtolower($game)){
				$game = $plugin;
			}
		}
		$time = time();
		$bug = mb_convert_encoding($bug, "UTF-8");
		$conf = new Config($this->path . "bugs.yml", Config::YAML, array());
		$bugs = $conf->getAll();
		$bugs[] = ["sender"=>$sender, "owner"=>$owner, "game"=>$game, "bug"=>$bug, "sql"=>$sql, "time"=>$time, "status"=>0];
		$conf->setAll($bugs);
		$conf->save();
		if($this->isConnected()){
			$this->db->query("INSERT INTO bugs
			(sender, owner, game, bug, time)
			VALUES
			('$sender', '$owner', '$game', '$bug', '$time')");
		}
	}
	
	private $commands = [
		"mgb" => "\\MGameBase\\command\\MgbCommand",
		"friend" => "\\MGameBase\\command\\FriendCommand",
		"mail" => "\\MGameBase\\command\\MailCommand",
		"hide" => "\\MGameBase\\command\\HideCommand",
		"show" => "\\MGameBase\\command\\ShowCommand",
		"lang" => "\\MGameBase\\command\\LangCommand"
	];
	
	private function registerCommands(){
		$map = $this->getServer()->getCommandMap();
		foreach($this->commands as $cmd => $class){
			$map->register("MGameBase", new $class($this));
		}
	}
	
	public function sendCommandHelp($sender){
		$msg = self::FORMAT."  §a[§7指令列表§a]";
		foreach($this->commands as $cmd => $class){
			$msg .= "\n§b[§7/§6".$cmd."§b] §f=> §7".Language::$message["ch"]["command.".$cmd];
		}
		$sender->sendMessage($msg);
	}

	public function sendInfo($sender){
		$sender->sendMessage(self::FORMAT."§6游戏币§7:§b".$this->getCoin($sender));
		$sender->sendMessage(self::FORMAT."§5小游戏VIP§7:§2".$this->getVIP($sender)." §8到期时间§7:§8".date("Y-m-d H:i:s",$this->getVIPTime($sender)));
	}
	
	public function getAllGames(){
		return $this->games;
	}
	
	public function isGameLevel($level){
		foreach($this->getAllGames() as $game){
			if($game->isGameLevel($level)){
				return true;
			}
		}
		return false;
	}
	
	public function isWaitLevel($level){
		foreach($this->getAllGames() as $game){
			if($game->isWaitLevel($level)){
				return true;
			}
		}
		return false;
	}
	
	public function isNormalLevel($level){
		foreach($this->getAllGames() as $game){
			if($game->isNormalLevel($level)){
				return true;
			}
		}
		return false;
	}
	
}
?>