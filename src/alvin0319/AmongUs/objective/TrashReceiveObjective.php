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
use function mt_rand;

class TrashReceiveObjective extends Objective{

	public function getName() : string{
		return "Trash receive";
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
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName("Trash receive");
		// 0~8: Iron bar
		// 9, 17: Iron bar
		// 18, 26: Iron bar
		// 27, 35: Iron bar
		// 36, 44: Iron bar
		// 45~53: Iron bar

		$bars = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 17, 18, 26, 27, 35, 36, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54];
		$barItem = ItemFactory::get(ItemIds::IRON_BARS, 0, 1);
		$barItem->setCustomName("§l ");
		foreach($bars as $index){
			$menu->getInventory()->setItem($index, $barItem);
		}
		$clearItem = ItemFactory::get(ItemIds::SIGN, 0, 1);
		$clearItem->setCustomName("§fClick to collect trash...");
		$clearItem->setNamedTagEntry(new StringTag("click"));
		$menu->getInventory()->setItem(43, $clearItem);

		$trashCount = mt_rand(3, 6);

		$c = 0;

		$trashItem = ItemFactory::get(ItemIds::TALL_GRASS, 0, 1);
		$trashItem->setCustomName("§cTrash");

		for($i = 0; $i < 54; $i++){
			if(!in_array($i, $bars) && $i !== 43){
				if(mt_rand(0, 3) === 2 && $c < $trashCount){
					$c++;
					$menu->getInventory()->setItem($i, $trashItem);
				}
			}
		}

		$menu->setListener(function(InvMenuTransaction $action) use ($player, $menu, $character, $game) : InvMenuTransactionResult{
			$player->getCursorInventory()->sendSlot(0, $player);

			$item = $action->getOut();

			if($item->getNamedTagEntry("click") === null){
				return $action->discard();
			}
			$first = $menu->getInventory()->first(ItemFactory::get(ItemIds::TALL_GRASS, 0, 1));

			if($first === -1){
				$character->completeObjective($this);
				$game->addProgress();
				$menu->onClose($player);
				return $action->discard();
			}
			$menu->getInventory()->setItem($first, ItemFactory::get(0));
			return $action->discard();
		});
		$menu->send($player);
	}
}