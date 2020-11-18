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
use alvin0319\AmongUs\game\Game;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\Server;

use function is_int;

class AmongUsHelpForm implements Form{

	public function jsonSerialize(){
		return [
			"type" => "form",
			"title" => "§cAmong§bUs §aMCPE",
			"content" => "§aChoose a option to proceed.",
			"buttons" => [
				["text" => "§aInfo"],
				["text" => "§aRoles"],
				["text" => "§aObjectives"],
				["text" => "§aCommands"],
				["text" => "§cExit Menu"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		switch($data){
			case 0:
				$player->sendMessage("§aPlugin made by Alvin0319");
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