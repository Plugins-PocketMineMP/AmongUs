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

namespace alvin0319\AmongUs\form\creation;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\game\Game;
use alvin0319\AmongUs\object\ObjectiveQueue;
use pocketmine\form\Form;
use pocketmine\Player;

use function count;
use function is_array;
use function is_int;
use function is_numeric;

class AmongUsGameCreateForm implements Form{

	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "Game Setup",
			"content" => [
				[
					"type" => "dropdown",
					"text" => "Map type",
					"options" => ["Skeld", "Polus"]
				],
				[
					"type" => "input",
					"text" => "Max imposters",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_MAX_IMPOSTERS]
				],
				[
					"type" => "input",
					"text" => "Max crewmates",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_MAX_CREW]
				],
				[
					"type" => "input",
					"text" => "Emergency time (second) (Conversation of emergency meeting and report)",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_EMERGENCY_TIME]
				],
				[
					"type" => "input",
					"text" => "Number of emergency calls",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_EMERGENCY_PRESS]
				],
				[
					"type" => "input",
					"text" => "Kill cooldown",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_KILL_COOLDOWN]
				],
				[
					"type" => "input",
					"text" => "Min player to start",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_MIN_PLAYER_TO_START]
				],
				[
					"type" => "input",
					"text" => "Waiting time",
					"default" => (string) Game::DEFAULT_SETTINGS[Game::SETTING_WAIT_SECOND]
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 8){
			return;
		}
		[$type, $maxImposters, $maxCrews, $emergencyTime, $emergencyCall, $coolDown, $minPlayer, $waitTime] = $data;
		if(!is_int($type)){
			return;
		}
		if(!is_numeric($maxImposters) || ($maxImposters = (int) $maxImposters) < 1){
			$player->sendMessage(AmongUs::$prefix . "Max number of imposters must be higher than 1.");
			return;
		}
		if(!is_numeric($maxCrews) || ($maxCrews = (int) $maxCrews) < 1){
			$player->sendMessage(AmongUs::$prefix . "Max number of crewmates must be higher than 1.");
			return;
		}
		if($maxImposters > $maxCrews){
			$player->sendMessage(AmongUs::$prefix . "Max number of crewmates must be higher than imposters.");
			return;
		}
		if(!is_numeric($emergencyTime) || ($emergencyTime = (int) $emergencyTime) < 60){
			$player->sendMessage(AmongUs::$prefix . "Time of emergency must be higher than 60. (1 minute)");
			return;
		}
		if(!is_numeric($emergencyCall) || ($emergencyCall = (int) $emergencyCall) < 1){
			$player->sendMessage(AmongUs::$prefix . "Number of emergency call must be higher than 1.");
			return;
		}
		if(!is_numeric($coolDown) || ($coolDown = (int) $coolDown) < 1){
			$player->sendMessage(AmongUs::$prefix . "Time of kill cooldown must be higher than 1.");
			return;
		}
		if(!is_numeric($minPlayer) || ($minPlayer = (int) $minPlayer) < 1){
			$player->sendMessage(AmongUs::$prefix . "Min number of players must be higher than 1.");
			return;
		}
		if(!is_numeric($waitTime) || ($waitTime = (int) $waitTime) < 10){
			$player->sendMessage(AmongUs::$prefix . "Time of wait must be higher than 10.");
			return;
		}
		ObjectiveQueue::$createQueue[$player->getName()] = [
			$type,
			$maxImposters,
			$maxCrews,
			$emergencyTime,
			$emergencyCall,
			$coolDown,
			$minPlayer,
			$waitTime
		];
		$player->sendMessage(AmongUs::$prefix . "Touch/Right-Click a block to set the spawnpoint for the game map.");
	}
}
