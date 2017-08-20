<?php
namespace MGameBase\event;

use MGameBase\MGameBase;
use pocketmine\event\plugin\PluginEvent;
class Event extends PluginEvent{
	public function __construct(MGameBase $plugin){
		parent::__construct($plugin);
	}
}