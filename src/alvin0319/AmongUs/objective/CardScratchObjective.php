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
 * Copyright (C) 2020 - 2021 alvin0319
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

namespace alvin0319\AmongUs\objective;

use alvin0319\AmongUs\AmongUs;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

use function in_array;

class CardScratchObjective extends Objective{

	public function getName() : string{
		return "Card scratch";
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
		$menu->setName("Scratch card");
		$cards = [9, 10, 11, 12, 13, 14, 15];

		$barItem = ItemFactory::get(ItemIds::IRON_BARS, 0, 1);
		$barItem->setCustomName("§l ");

		$notScratch = ItemFactory::get(ItemIds::STAINED_GLASS_PANE, 14, 1);
		$notScratch->setCustomName("Click me to scratch card...");
		$notScratch->setNamedTagEntry(new StringTag("scratch"));
		$scratch = ItemFactory::get(ItemIds::STAINED_GLASS_PANE, 5, 1);

		for($i = 0; $i < 27; $i++){
			if(!in_array($i, $cards)){
				$menu->getInventory()->setItem($i, $barItem);
			}else{
				$menu->getInventory()->setItem($i, $notScratch);
			}
		}

		$menu->setListener(function(InvMenuTransaction $action) use ($player, $menu, $game, $character, $scratch, $notScratch, $cards) : InvMenuTransactionResult{
			$player->getCursorInventory()->sendSlot(0, $player);

			$item = $action->getOut();

			if($item->getNamedTagEntry("scratch") === null){
				return $action->discard();
			}

			$slot = $action->getAction()->getSlot();

			$menu->getInventory()->setItem($slot, $scratch);

			if($slot === 15){
				$valid = true;
				foreach($cards as $cardSlot){
					if(!$menu->getInventory()->getItem($cardSlot)->equals($scratch)){
						$valid = false;
					}
				}
				if(!$valid){
					foreach($cards as $i){
						$menu->getInventory()->setItem($i, $notScratch);
					}
					return $action->discard();
				}
				$menu->onClose($player);
				$character->completeObjective($this);
				$game->addProgress();
				return $action->discard();
			}

			return $action->discard();
		});
	}
}