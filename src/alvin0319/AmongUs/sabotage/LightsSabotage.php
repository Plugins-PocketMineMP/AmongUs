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

namespace alvin0319\AmongUs\sabotage;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\Player;

class LightsSabotage extends Sabotage{

	public function onActivate(Player $player) : void{
		foreach($this->game->filterCrewmates() as $crewmate){
			$crewmate->getPlayer()->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20 * 1000, 0));
		}
		$this->game->broadcastMessage("Lights have been Sabotaged");
	}

	public function onInteract(Player $player) : void{
		$item = $player->getInventory()->getItemInHand();
		if($item->getId() == 290 and $item->getCustomName() == 'fix'){
			foreach($this->game->filterCrewmates() as $crewmate){
				$crewmate->getPlayer()->removeAllEffects();
			}
			$this->game->broadcastMessage("§aLights fixed!");
		}
	}
}