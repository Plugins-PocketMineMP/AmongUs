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

namespace alvin0319\AmongUs\object;

use alvin0319\AmongUs\AmongUs;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\BlockIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\sound\GenericSound;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class FileReceiveObjective extends Objective{

	public function getName() : string{
		return "File receive";
	}

	public function onInteract(Player $player) : void{
		$game = AmongUs::getInstance()->getGameByPlayer($player);
		if($game === null){
			return;
		}
		$character = $game->getCharacter($player);
		if($character === null){
			return;
		}
		if($character->isCompletedObjective($this)){
			return;
		}
		if(!$character->hasObjective($this)){
			return;
		}
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName("File Download");
		$inv = $menu->getInventory();

		$ironBar = ItemFactory::get(ItemIds::IRON_BARS);
		$ironBar->setCustomName("§l ");
		$inv->setItem(10, $ironBar);
		$inv->setItem(16, $ironBar);

		$bed = ItemFactory::get(BlockIds::BED_BLOCK);
		$bed->setCustomName("§lStart Downloading");
		$bed->setNamedTagEntry(new IntTag("start"));
		$inv->setItem(22, $bed);
		//10(iron_bars), 16(iron_bars), 22(bed)

		ObjectiveQueue::$fileReceiveQueue[$player->getName()] = false;

		$menu->setInventoryCloseListener(function(Player $player) use ($character, $game) : void{
			if(ObjectiveQueue::$fileReceiveQueue[$player->getName()]){
				$character->completeObjective($this);
				$game->addProgress();
			}
			unset(ObjectiveQueue::$fileReceiveQueue[$player->getName()]);
		});

		$menu->setListener(function(InvMenuTransaction $action) use ($menu) : InvMenuTransactionResult{
			$player = $action->getPlayer();
			$item = $action->getOut();
			if($item->getNamedTagEntry("start") !== null){
				$c = 0;
				$handler = AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $unused) use ($menu, $player, &$handler, $c) : void{
					if(++$c < 4){
						$slot = 15 - (4 - $c);
						$menu->getInventory()->setItem($slot, ItemFactory::get(ItemIds::EMERALD));
					}else{
						ObjectiveQueue::$fileSendQueue[$player->getName()] = true;
						$menu->onClose($player);
						$handler->cancel();
					}
				}), 20);
			}
			$player->getCursorInventory()->sendSlot(0, $player);
			return $action->discard();
		});
	}
}
