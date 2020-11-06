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
use pocketmine\form\Form;
use pocketmine\Player;

use function is_int;

class AmongUsMainForm implements Form{

	public function jsonSerialize(){
		return [
			"type" => "form",
			"title" => "Play Among Us in Minecraft!",
			"content" => "",
			"buttons" => [
				["text" => "Exit"],
				["text" => "Join the game"],
				["text" => "See rules"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		switch($data){
			case 1:
				$game = AmongUs::getInstance()->getAvailableGame($player);
				if($game === null){
					$player->sendMessage(AmongUs::$prefix . "There are no available game. (already joined or all games are running)");
					return;
				}
				$game->addPlayer($player);
				break;
			case 2:
				// TODO: implement rules
				break;
		}
	}
}