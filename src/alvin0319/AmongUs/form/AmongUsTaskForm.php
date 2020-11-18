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
use alvin0319\AmongUs\form\crew\ManifoldOpenObjectiveForm;
use alvin0319\AmongUs\Crewmate\object\ManifoldOpenObjective;
use alvin0319\AmongUs\Crewmate\object\EnergyChangeObjective;
use alvin0319\AmongUs\Crewmate\object\FileReceiveObjective;
use alvin0319\AmongUs\Crewmate\object\FileSendObjective;
use alvin0319\AmongUs\Crewmate\object\Objective;
use alvin0319\AmongUs\Crewmate\object\ObjectiveQueue;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use alvin0319\AmongUs\task\DisplayTextTask;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityLevelChangeEvent;

use function is_int;

class AmongUsTaskForm implements Form{

	public function jsonSerialize(){
		return [
			"type" => "form",
			"title" => "§cAmong§bUs §aMCPE",
			"content" => "see where the task are.",
			"buttons" => [
				["text" => "§aEnergy Change"],
				["text" => "§aFile Receive"],
				["text" => "§aFile Send"],
				["text" => "§aManifold Open"],
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
				$game = AmongUs::getInstance()->getGameByPlayer($player);
				if($game === null){
					$player->sendMessage(AmongUs::$prefix . "§aComeplte the Task to win");
					$player->sendMessage("The Task is in Storage, Search for a Emerald Block");
				return;
			}
			  break;
			 case 1:
				$game = AmongUs::getInstance()->getGameByPlayer($player);
				if($game === null){
					$player->sendMessage(AmongUs::$prefix . "§aComeplte the Task to win");
					$player->sendMessage("The Task is in Cafeteria, Search for Iron Block");
				return;
			}
			  break;
			case 2:
				$game = AmongUs::getInstance()->getGameByPlayer($player);
				if($game === null){
					$player->sendMessage(AmongUs::$prefix . "§aComeplte the Task to win");
					$player->sendMessage("The Task is in Admin, Search for a Gold Block");
				return;
			}
			  break;
			case 3:
				$game = AmongUs::getInstance()->getGameByPlayer($player);
				if($game === null){
					$player->sendMessage(AmongUs::$prefix . "§aComeplte the Task to win");
					$player->sendMessage("The Task is in Reactor, Search for a Iron Block");
					return;
			}
			  break;
		}
	}
}