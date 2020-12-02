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

namespace alvin0319\AmongUs\objective;

use alvin0319\AmongUs\AmongUs;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Color;

use function mt_rand;

class ScanObjective extends Objective{

	public function getName() : string{
		return "Scan body";
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

		$c = 0;

		$player->sendTitle("Scanning...", "Don't move!");

		$handler = null;
		$handler = AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $unused) use ($player, &$handler, $character, $game, &$c) : void{
			$color = (new Color(0, 255, 0))->toRGBA();
			$particle = new class(new Vector3(), Particle::TYPE_SPARKLER, $color) extends GenericParticle{
			};
			for($i = 0; $i < 2; $i++){
				$vec = new Vector3($player->getX() + mt_rand(-3, 3), $player->getY() + mt_rand(-3, 3), $player->getZ() + mt_rand(-3, 3));
				$particle->setComponents($vec->x, $vec->y, $vec->z);
				$player->getLevel()->addParticle($particle);
			}
			$c++;
			if($c >= 4){
				$handler->cancel();
				$handler = null;
				$character->completeObjective($this);
				$game->addProgress();
				$player->sendTitle("Scanning complete!", "You are crewmate!");
			}
		}), 20);
	}
}