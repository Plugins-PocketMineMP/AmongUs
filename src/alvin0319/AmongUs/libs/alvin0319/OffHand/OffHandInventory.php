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

use BadMethodCallException;
use pocketmine\entity\Entity;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;

class OffHandInventory extends BaseInventory{

	/** @var Player */
	protected $holder;

	public function __construct(Player $holder){
		parent::__construct([], 1);
		$this->holder = $holder;
	}

	public function getPlayer() : Player{
		return $this->holder;
	}

	public function setSize(int $size) : void{
		throw new BadMethodCallException("Cannot call setSize on OffHandInventory");
	}

	public function getName() : string{
		return "OffHandInventory";
	}

	public function getDefaultSize() : int{
		return 1;
	}

	public function setItemInOffHand(Item $item) : void{
		$this->setItem(0, $item);
		
		$pk = new InventoryContentPacket();
		$pk->windowId = $this->holder->getWindowId($this);
		$pk->items = [ItemStackWrapper::legacy($item)];
		$this->holder->sendDataPacket($pk);

		$this->holder->getDataPropertyManager()->setByte(Entity::DATA_COLOR, Entity::DATA_TYPE_BYTE);

		$this->sendMobEquipmentPacket($this->holder);

		foreach($this->viewers as $player){
			$this->sendMobEquipmentPacket($player);
		}
	}

	public function getItemInOffHand() : Item{
		return $this->getItem(0);
	}

	public function sendMobEquipmentPacket(Player $player) : void{
		$pk = new MobEquipmentPacket();
		$pk->windowId = $this->getPlayer()->getWindowId($this);
		$pk->item = $this->getItemInOffHand();
		$pk->entityRuntimeId = $this->getPlayer()->getId();
		$pk->hotbarSlot = $pk->inventorySlot = 0;
		$player->sendDataPacket($pk);
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void{
		foreach($this->viewers as $player){
			$this->sendMobEquipmentPacket($player);
		}
	}
}
