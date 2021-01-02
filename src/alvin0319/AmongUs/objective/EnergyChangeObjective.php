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
use pocketmine\item\ItemIds;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

use function in_array;

class EnergyChangeObjective extends Objective{

	public function getName() : string{
		return "Energy change";
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
		$menu->setName("Energy change");

		$slots = [
			2,
			3,
			4,
			5,
			6,
			11,
			12,
			13,
			14,
			15,
			20,
			21,
			22,
			23,
			24
		];

		$menu->setListener(function(InvMenuTransaction $action) use ($menu, $slots, $game, $character) : InvMenuTransactionResult{
			$player = $action->getPlayer();

			if(!in_array($action->getAction()->getSlot(), $slots)){
				$player->getCursorInventory()->sendSlot(0, $player);
				return $action->discard();
			}

			$player->getCursorInventory()->sendSlot(0, $player);

			$valid = true;

			for($i = 11; $i < 16; $i++){
				if($menu->getInventory()->getItem($i)->getId() !== ItemIds::EMERALD){
					$valid = false;
				}
			}
			if($valid){
				$menu->getInventory()->onClose($player);
				$character->completeObjective($this);

				$game->addProgress();

				return $action->continue()->then(function(Player $o) : void{
					$o->getCursorInventory()->clearAll();
				});
			}

			return $action->continue();
		});
	}
}