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

namespace alvin0319\AmongUs\form\crew;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\object\ManifoldOpenObjective;
use pocketmine\form\Form;
use pocketmine\Player;

use function strlen;

class ManifoldOpenObjectiveForm implements Form{
	/** @var string */
	protected $progress = "";
	/** @var ManifoldOpenObjective */
	protected $objective;

	public function __construct(ManifoldOpenObjective $objective, string $progress){
		$this->objective = $objective;
		$this->progress = $progress;
	}

	public function jsonSerialize() : array{
		$serialized = [
			"type" => "text",
			"title" => "Please input the number",
			"content" => "Progress: " . $this->progress,
			"buttons" => []
		];
		for($i = 0; $i < 9; $i++){
			$serialized["buttons"][] = ["text" => (string) $i];
		}
		return $serialized;
	}

	public function handleResponse(Player $player, $data) : void{
		$this->progress .= (string) $data;
		$game = AmongUs::getInstance()->getGameByPlayer($player);
		if($game === null){
			return;
		}
		$character = $game->getCharacter($player);
		if($character === null){
			return;
		}
		if(strlen($this->progress) === 10){
			if($this->progress === "0123456789"){
				// Objective complete
				$character->completeObjective($this->objective);
				$game->addProgress();
			}else{
				$player->sendMessage(AmongUs::$prefix . "Invalid input, Try again.");
			}
		}else{
			$player->sendForm($this);
		}
	}
}
