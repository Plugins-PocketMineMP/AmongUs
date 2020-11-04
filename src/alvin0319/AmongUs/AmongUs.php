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

namespace alvin0319\AmongUs;

use alvin0319\AmongUs\game\Game;
use alvin0319\AmongUs\item\Map;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class AmongUs extends PluginBase{
	/** @var string */
	public static $prefix = "§b§l[AmongUs] §r§7";
	/** @var AmongUs|null */
	private static $instance = null;
	/** @var Game[] */
	protected $games = [];

	public function onLoad() : void{
		self::$instance = $this;
	}

	public static function getInstance() : AmongUs{
		return self::$instance;
	}

	public function onEnable() : void{
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$this->saveDefaultConfig();

		ItemFactory::registerItem(new Map(ItemIds::FILLED_MAP, 0, "Filled Map"));
	}

	public function registerGame(Game $game) : void{
		$this->games[$game->getId()] = $game;
	}

	public function getGame(int $id) : ?Game{
		return $this->games[$id] ?? null;
	}

	public function getGameByPlayer(Player $player) : ?Game{
		foreach($this->games as $game){
			if($game->hasPlayer($player)){
				return $game;
			}
		}
		return null;
	}
}