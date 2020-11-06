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
use alvin0319\AmongUs\form\crew\ManifoldOpenObjectiveForm;
use pocketmine\Player;

class ManifoldOpenObjective extends Objective{

	public function getName() : string{
		return "Manifold open";
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
		$player->sendForm(new ManifoldOpenObjectiveForm($this, ""));
	}
}