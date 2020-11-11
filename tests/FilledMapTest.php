<?php

/**
 *      _                                _   _
 *    / \   _ __ ___   ___  _ __   __ _| | | |___
 *   / _ \ | '_ ` _ \ / _ \| '_ \ / _` | | | / __|
 *  / ___ \| | | | | | (_) | | | | (_| | |_| \__ \
 * /_/   \_\_| |_| |_|\___/|_| |_|\__, |\___/|___/
 *                                |___/
 * A PocketMine-MP plugin that implements AmongUs
 * Copyright (C) 2020 alvin0319
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @name FilledMapTest
 * @author  alvin0319
 * @main    alvin0319\AmongUs\tests\FilledMapTest
 * @version 1.0.0
 * @api     3.0.0
 */

declare(strict_types=1);

namespace alvin0319\AmongUs\tests;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Color;
use pocketmine\utils\Config;

class FilledMapTest extends PluginBase implements Listener{

	protected $mapIdCounter = 0;

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->mapIdCounter = (int) (new Config($this->getDataFolder() . "MapIdCounter.yml", Config::YAML, [
			"id" => 0
		]))->get("id", 0);
	}

	public function onDisable() : void{
		$config = new Config($this->getDataFolder() . "MapIdCounter.yml", Config::YAML, []);
		$config->set("id", $this->mapIdCounter);
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		if($packet instanceof MapInfoRequestPacket){
			$this->sendMapPacket($event->getPlayer(), $packet->mapId);
			$event->setCancelled(); // Tells the pmmp to ignore this packet
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($item->getId() === ItemIds::MAP){
			$event->setCancelled();
			$item->pop();
			$player->getInventory()->setItemInHand($item);
			$item = ItemFactory::get(ItemIds::FILLED_MAP);
			$item->getNamedTag()->setLong("map_uuid", ++$this->mapIdCounter);
			$player->getInventory()->addItem($item);
		}
	}

	public function sendMapPacket(Player $player, int $mapId) : void{
		$packet = new ClientboundMapItemDataPacket();
		$packet->mapId = $mapId;
		$packet->type = ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE;
		$packet->scale = 1;
		$packet->width = 128;
		$packet->height = 128;

		for($y = 0; $y < 128; $y++){
			for($x = 0; $x < 128; $x++){
				$packet->colors[$y][$x] = new Color(128, 0, 0);
			}
		}
		$player->sendDataPacket($packet);
	}
}