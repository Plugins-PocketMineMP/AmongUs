<?php

/*
 *      _                                _   _
 *    / \   _ __ ___   ___  _ __   __ _| | | |___
 *   / _ \ | '_ ` _ \ / _ \| '_ \ / _` | | | / __|
 *  / ___ \| | | | | | (_) | | | | (_| | |_| \__ \
 * /_/   \_\_| |_| |_|\___/|_| |_|\__, |\___/|___/
 *                                |___/
 *
 * A PocketMine-MP plugin that implements AmongUs
 *
 * Copyright (C) 2020 alvin0319
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author alvin0319
 */

declare(strict_types=1);

namespace alvin0319\AmongUs\Impostor\sabotage;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\block\Block;
use alvin0319\AmongUs\EventListener;
use alvin0319\AmongUs\AmongUs;
use pocketmine\utils\Config;
use alvin0319\AmongUs\form\SabotageForm;

abstract class Sabotage{
	/** @var Position */
	protected $pos;

	public $sabcooldown;

	public function __construct(Position $pos){
		$this->pos = $pos;
		$this->sabcooldown = $this->plugin->getConfig()->get("cooldown");
	}

	final public function getPosition() : Position{
		return $this->pos;
	}

	/**
	 * Called when imposters activate sabotage
	 *
	 * @param Player $player
	 */
	abstract public function onActivate(Player $player) : void;

	/**
	 * Called when crewmates or imposters interact sabotage
	 *
	 * @param Player $player
	 */
	public function onInteract(PlayerInteractEvent $event){
	    $player = $event->getPlayer();
	    $item = $event->getItem();
	    $block = $event->getBlock();
	 if($item->getID() == 409 and $item->getCustomName() == 'Sabotage'){
	   		$player->sendForm(new SabotageForm($this, ""));
	   	 }
	  }
	
	public function getCool() : int{
		return 5; 
	}
}