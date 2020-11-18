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
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityLevelChangeEvent;

use function is_int;

class AmongUsMainForm implements Form{

	public function jsonSerialize(){
		return [
			"type" => "form",
			"title" => "§cAmong§bUs §aMCPE",
			"content" => "§aChoose a option.",
			"buttons" => [
				["text" => "§aPlay"],
				["text" => "§aHow to play"],
				["text" => "§eLeave Game"],
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
				$game = AmongUs::getInstance()->getAvailableGame($player);
				if($game === null){
				$player->sendMessage(AmongUs::$prefix . "There are no available games right now. (all games are currently running!)");
				return;
				}
				$game->addPlayer($player);
				break;
			case 1:
			    $lines = "§b-------------------------------------------";
			    $space = " ";
			    $player->sendTip("§aInfo Message Sent!");
			    $player->sendMessage($lines . "\n" . "§8-=[§a+§8]§b=-§l§cAmong§bUs §r§ain §aMCPE §b-=§8[§a+§8]=-" . "\n" . "\n" . $space . $space . $space . "§6Intro:" . "\n" . "§cAmong§bUs §eis a game of teamwork & betrayal." . "\n" . "§ePlayers are either Crewmates or an Impostor." . "\n" . $space . $space . $space . "§6Roles:" . "\n" . "§bCrewmate: Complete the tasks to win." . "\n" . "§cImposter: Kill all Crewmates to win." . "\n" . $space . $space . $space . "§6Info:" . "\n" . "§eDuring Meetings make sure to discuss on who to vote out. (vote out the imposter)" . "\n" . "§ePlayers have access to a personal map to help navigate through the map" . "\n" . "§8-=[§a+§8]=- [§aEnjoy Playing§8] -=[§a+§8]=-" . "\n" . $lines);
				break;
			case 2:
			  $game = AmongUs::getInstance()->getGameByPlayer($player);
			  if($game !== null){
				$player->sendMessage(AmongUs::$prefix . "Test 1 - Leave");
				$player->teleport($player->getServer()->getDefaultLevel()->getSafeSpawn());
				$game->removePlayer($player);
				$player->getInventory()->clearAll();
			  $player->setInvisible(false);
			    return;
			}
			$player->sendMessage(AmongUs::$prefix . "Test 2 - Error");
			break;
		}
	}
}