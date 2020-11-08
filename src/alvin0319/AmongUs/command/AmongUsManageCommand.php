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
use alvin0319\AmongUs\form\creation\AmongUsGameCreateForm;
use alvin0319\AmongUs\item\FilledMap;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Player;

use function is_numeric;
use function trim;

class AmongUsManageCommand extends PluginCommand{

	public function __construct(){
		parent::__construct("amongusmanage", AmongUs::getInstance());
		$this->setDescription("Manage AmongUS Game setting");
		$this->setPermission("amongus.command.manage");
		$this->setAliases(["aum", "amum"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(AmongUs::$prefix . "This command can be only executed in-game");
			return false;
		}
		switch($args[0] ?? "x"){
			case "creategame":
				$sender->sendForm(new AmongUsGameCreateForm());
				break;
			case "createobjective":
				break;
			case "setmapimage":
				if(trim($args[1] ?? "") === ""){
					$sender->sendMessage(AmongUs::$prefix . "Usage: /{$commandLabel} setmapimage [gameId]");
					return false;
				}
				if(!is_numeric($args[1]) || (int) $args[1] < 0){
					$sender->sendMessage(AmongUs::$prefix . "Usage: /{$commandLabel} setmapimage [gameId]");
					return false;
				}
				$game = AmongUs::getInstance()->getGame((int) $args[1]);
				if($game === null){
					$sender->sendMessage(AmongUs::$prefix . "No Game id with {$args[1]} found.");
					return false;
				}
				/** @var FilledMap $item */
				$item = ItemFactory::get(ItemIds::FILLED_MAP);
				$item->setMapData($sender);
				$item->setMapId(0);
				//$item->setDisplayPlayers(true);
				$game->setMapItem($item);
				$sender->getInventory()->addItem($item);
				$sender->sendMessage(AmongUs::$prefix . "Successfully completed the game setup!);
				break;
			default:
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} setmapimage [gameId]");
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} creategame");
				$sender->sendMessage(AmongUs::$prefix . "/{$commandLabel} createobjective");
		}
		return true;
	}
}
