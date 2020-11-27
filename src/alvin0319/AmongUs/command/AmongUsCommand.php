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

namespace alvin0319\AmongUs\command;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\form\AmongUsMainForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class AmongUsCommand extends PluginCommand{

	public function __construct(){
		parent::__construct("amongus", AmongUs::getInstance());
		$this->setPermission("amongus.command");
		$this->setDescription("Open the AmongUs Game UI");
		$this->setAliases(["au", "amu"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(AmongUs::$prefix . "This command can be only executed In-Game.");
			return false;
		}
		switch($args[0] ?? "x"){
			case "join":
				$game = AmongUs::getInstance()->getAvailableGame($sender);
				if($game === null){
					$sender->sendMessage(AmongUs::$prefix . "There are no available games right now. (all games are currently running!)");
					break;
				}
				$game->addPlayer($sender);
				break;
			case "info":
				$lines = "§b------------------------------------------";
				$space = " ";
				$sender->sendMessage($lines . "\n" . "§8-=[§a+§8]§b=-§l§cAmong§bUs §r§ain §aMCPE §b-=§8[§a+§8]=-" . "\n" . "\n" . $space . $space . $space . $space . $space . " §6Intro:" . "\n" . "§cAmong§bUs §eis a game of teamwork & betrayal." . "\n" . "§ePlayers are either Crewmates or an Impostor." . "\n" . $space . $space . $space . $space . $space . " §6Roles:" . "\n" . " — §bCrewmate: §eComplete the tasks to win." . "\n" . " — §cImposter: §eKill all Crewmates to win." . "\n" . $space . $space . $space . $space . $space . " §6Info:" . "\n" . "§eDuring Meetings make sure to discuss on who to vote out. (vote out the imposter)" . "\n" . "§ePlayers have access to a personal map to help navigate through the map" . "\n" . "\n" . "§8-=[§a+§8]=- [§aEnjoy Playing§8] -=[§a+§8]=-" . "\n" . $lines);
				break;
			case "leave": // leave and quit is same
			case "quit":
				$game = AmongUs::getInstance()->getGameByPlayer($sender);
				if($game === null){
					$sender->sendMessage(AmongUs::$prefix . "You are not in a game.");
					break;
				}
				$game->removePlayer($sender);
				$sender->getInventory()->clearAll();
				$sender->teleport($sender->getServer()->getDefaultLevel()->getSafeSpawn());
				$sender->sendMessage(AmongUs::$prefix . "Left the game #{$game->getId()}.");
				break;
			default:
				$sender->sendForm(new AmongUsMainForm());
		}
		return true;
	}
}
