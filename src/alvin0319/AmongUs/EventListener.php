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

namespace alvin0319\AmongUs;

use alvin0319\AmongUs\character\Crew;
use alvin0319\AmongUs\character\Imposter;
use alvin0319\AmongUs\entity\DeadPlayerEntity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;

class EventListener implements Listener{

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		if(!$packet instanceof InventoryTransactionPacket){
			return;
		}
		if($packet->transactionType !== InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
			return;
		}
		$entity = $player->getServer()->findEntity($packet->trData->entityRuntimeId);
		if(!$entity instanceof DeadPlayerEntity){
			return;
		}
		$entity->interact($player);
	}

	public function onEntityDamage(EntityDamageByEntityEvent $event) : void{
		$victim = $event->getDamager();
		$entity = $event->getEntity();
		if(!$victim instanceof Player || !$entity instanceof Player){
			return;
		}
		if(AmongUs::getInstance()->getGameByPlayer($victim) === null || AmongUs::getInstance()->getGameByPlayer($entity) === null || AmongUs::getInstance()->getGameByPlayer($victim) !== AmongUs::getInstance()->getGameByPlayer($entity)){
			return;
		}
		$game = AmongUs::getInstance()->getGameByPlayer($victim);
		$victimCharacter = $game->getCharacter($victim);
		$entityCharacter = $game->getCharacter($entity);
		if($victimCharacter === null || $entityCharacter === null){
			return;
		}
		if(!$victimCharacter instanceof Imposter){
			return;
		}
		if(!$entityCharacter instanceof Crew){
			return;
		}
		$game->killPlayer($entity, $victim);
	}

	public function onPlayerChat(PlayerChatEvent $event) : void{

	}
}