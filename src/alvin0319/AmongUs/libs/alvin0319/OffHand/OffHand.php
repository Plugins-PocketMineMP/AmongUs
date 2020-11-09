<?php

/*
 *       _       _        ___ _____ _  ___
 *   __ _| |_   _(_)_ __  / _ \___ // |/ _ \
 * / _` | \ \ / / | '_ \| | | ||_ \| | (_) |
 * | (_| | |\ V /| | | | | |_| |__) | |\__, |
 *  \__,_|_| \_/ |_|_| |_|\___/____/|_|  /_/
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
 */

declare(strict_types=1);

namespace alvin0319\AmongUs\libs\alvin0319\OffHand;

use pocketmine\event\Listener;
use alvin0319\AmongUs\AmongUs;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use function array_merge;

class OffHand extends PluginBase implements Listener{

	/** @var OffHandInventory[] */
	protected $inventories = [];

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$this->inventories[$event->getPlayer()->getName()] = new OffHandInventory($event->getPlayer());
		$this->loadInventory($event->getPlayer());
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$this->saveInventory($event->getPlayer());
	}

	private function loadInventory(Player $player) : void{
		$player->addWindow($this->getOffHandInventory($player), ContainerIds::OFFHAND, true);
		if($player->namedtag->hasTag("OffHand", CompoundTag::class)){
			$this->getOffHandInventory($player)->setItemInOffHand(Item::nbtDeserialize($player->namedtag->getCompoundTag("OffHand")));
		}
	}

	private function saveInventory(Player $player) : void{
		$player->namedtag->setTag($this->getOffHandInventory($player)->getItemInOffHand()->nbtSerialize(-1, "OffHand"));
		unset($this->inventories[$player->getName()]);
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		if($packet instanceof MobEquipmentPacket){
			if($packet->windowId === ContainerIds::OFFHAND){
				$inv = $this->getOffHandInventory($player);
				if($inv instanceof OffHandInventory){
					$inv->setItemInOffHand($packet->item);
					$event->setCancelled();
				}
			}
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $event) : void{
		$packet = $event->getPacket();
		if($packet instanceof AddPlayerPacket){
			if(($player = $this->getServer()->getPlayerExact($packet->username)) !== null){
				$this->getOffHandInventory($event->getPlayer())->sendMobEquipmentPacket($player);
				$this->getOffHandInventory($event->getPlayer())->sendMobEquipmentPacket($event->getPlayer());
			}
		}
	}

	public function onDeath(PlayerDeathEvent $event) : void{
		$player = $event->getPlayer();
		$drops = $event->getDrops();
		if(!$event->getKeepInventory()){
			$drops = array_merge($drops, $this->getOffHandInventory($player)->getContents(false));
			$event->setDrops($drops);
			$this->getOffHandInventory($player)->clearAll();
		}
	}

	public function getOffHandInventory(Player $player) : ?OffHandInventory{
		return $this->inventories[$player->getName()] ?? null;
	}
}