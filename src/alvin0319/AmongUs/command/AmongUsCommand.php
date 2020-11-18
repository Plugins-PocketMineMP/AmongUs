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
use alvin0319\AmongUs\form\AmongUsHelpForm;
use alvin0319\AmongUs\form\AmongUsTaskForm;
use alvin0319\AmongUs\game\Game;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\Item;
use pocketmine\Player;

class AmongUsCommand extends PluginCommand{

	public function __construct(){
		parent::__construct("amongus", AmongUs::getInstance());
		$this->setPermission("amongus.command");
		$this->setDescription("Main AmongUs Command");
		$this->setAliases(["au", "amu"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(AmongUs::$prefix . "This command can be only executed in-game.");
			return false;
		}
		switch($args[0] ?? "x"){
			case "play":
			$sender->sendForm(new AmongUsMainForm());
				break;
			case "forcejoin":
				$game = AmongUs::getInstance()->getAvailableGame($sender);
				$game->addPlayer($sender);
				break;
			case "forceleave":
			  $game = AmongUs::getInstance()->getAvailableGame($sender);
			  $game->removePlayer($sender);
			  break;
			case "help":
				$sender->sendForm(new AmongUsHelpForm());
				break;
			case "tasks":
				$sender->sendForm(new AmongUsTaskForm());
				break;
			default:
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} play");
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} forcejoin");
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} forceleave");
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} help");
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} tasks");
		}
		return true;
	}
}