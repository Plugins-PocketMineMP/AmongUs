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

use function is_int;

class AmongUsMainForm implements Form{

	public function jsonSerialize(){
		return [
			"type" => "form",
			"title" => "§cAmong§eUs §aMCPE",
			"content" => "",
			"buttons" => [
				["text" => " §aJoin a match"],
				["text" => " §aHow to play"],
				["text" => " §aPlugin Info"],
				["text" => " §cExit Menu"]
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
					$player->sendMessage(AmongUs::$prefix . "There are no available games right now. (all games are currently running!)");
					return;
				}
				$game->addPlayer($player);
				break;
			case 2:
			    $lines = "§8------------------------------------------------------";
			    $space = " ";
			    $player->sendTip("Plugin Credits: Alvin0319");
			    $player->sendMessage($lines . "\n" . "§8-=[§a+§8]§b=-§l§cAmong§eUs §r§ain §aMCPE §b-=§8[§a+§8]=-" . "\n" . $space . "\n" . "§6Intro:" . "\n" . $space . "\n" . "§eAmongUS is a game of teamwork & betrayal." . "\n" . "§ePlayers are either Crewmates or an Impostor." . "\n" . $space . "\n" . $space . "\n" . "§6Roles:" . "\n" . $space . "\n" . "§bCrewmate: Complete the tasks to win." . "\n" . "§cImposter: Kill all Crewmates to win." . "\n" . $space . "\n" . "§6Info:" . "\n" . $space . "\n" . "§eDuring Meetings make sure to discuss on who to vote out. (vote out the imposter)" . "\n" . $space . "§ePlayers have access to a personal map to help navigate through the map" . "\n" . $space . "§8-=[§a+§8]=- [§aHave Fun Playing§8] -=[§a+§8]=-" . "\n" . $lines);
				break;
			case 3:
			    $line = "§8------------------------------------------------------";
			    $space = " ";
			    $player->sendMessage($lines . "\n" . "§8-=[§a+§8]§b=-§l§cAmong§eUs §r§ain §aMCPE §b-=§8[§a+§8]=-" . "\n" . $space . "\n" . "§6Plugin Info:" . "\n" . $space . "\n" . "§6Name:" . "\n" . "§a§cAmong§eUs§r" . "\n" . $space . "\n" . "§6Author:" . "\n" . $space . "\n" . "§aAlvin0319" . "\n" . $space . "\n" . $space . "§8-=[§a+§8]=- [§cAmong§eUs §aby Alvin0319§8] -=[§a+§8]=-" . "\n" . $lines);
				break;
			case 4:
			    $player->sendTip("§aSuccessfully Closed Game Menu");
			    break;
		}
	}
}
