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

namespace alvin0319\AmongUs\form;

use alvin0319\AmongUs\AmongUs;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\form\Form;
use pocketmine\Player;

use function is_int;

class SabotageForm implements Form{

	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "§6Sabotage Menu",
			"content" => "§eChoose a option to Sabotage!",
			"buttons" => [
				["text" => "§aLights"],
				["text" => "§aOxygen"],
				["text" => "§aReactor"],
				["text" => "§cExit Menu"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		$game = AmongUs::getInstance()->getGameByPlayer($player);
		switch($data){
			case 0:
				$player->sendTip("§eLights Sabotaged!");
				$player->sendMessage("Error 1");
				break;
			case 1:
				$player->sendMessage("Comming soon");
				break;
			case 2:
				$player->sendMessage("Comming soon");
				break;
			case 3:
				$player->sendMessage("Comming soon");
				break;
		}
	}
}