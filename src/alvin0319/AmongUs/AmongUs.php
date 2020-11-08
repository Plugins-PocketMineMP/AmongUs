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

use alvin0319\AmongUs\command\AmongUsCommand;
use alvin0319\AmongUs\command\AmongUsManageCommand;
use alvin0319\AmongUs\entity\DeadPlayerEntity;
use alvin0319\AmongUs\game\Game;
use alvin0319\AmongUs\item\FilledMap;
use alvin0319\AmongUs\object\Objective;
use alvin0319\AmongUs\task\WorldCopyAsyncTask;
use alvin0319\AmongUs\task\WorldDeleteAsyncTask;
use Closure;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function json_decode;
use function json_encode;

class AmongUs extends PluginBase{
	/** @var string */
	public static $prefix = "§a§l[§cAmong§eUs§a] §r>§7 ";
	/** @var AmongUs|null */
	private static $instance = null;
	/** @var Game[] */
	protected $games = [];
	/** @var Objective[][] */
	protected $objectives = [];

	protected $data = [];

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

		Entity::registerEntity(DeadPlayerEntity::class, true, ["DeadPlayerEntity"]);

		ItemFactory::registerItem(new FilledMap(ItemIds::FILLED_MAP, 0, "Filled Map"), true);

		if(file_exists($file = $this->getDataFolder() . "AmongUsData.json")){
			$this->data = json_decode(file_get_contents($file), true);
		}

		for($i = 0; $i < $this->getConfig()->get("max_games"); $i++){
			$gameData = $this->data[$i] ?? null;
			if($gameData === null){
				continue;
			}
			$objectives = [];
			foreach($gameData["objectives"] ?? [] as $objectiveName => $objectiveData){
				$objective = Objective::getByName($objectiveName, $objectiveData);
				if($objective === null){
					continue;
				}
				$objectives[] = $objective;
			}

			[$x, $y, $z, $world] = explode(":", $gameData["spawnPos"]);

			$map = isset($gameData["mapItem"]) ? Item::jsonDeserialize($gameData["mapItem"]) : null;

			$game = new Game($i, $gameData["map"], new Position((float) $x, (float) $y, (float) $z, $this->getServer()->getLevelByName($world)), $objectives, $map, $gameData["settings"] ?? Game::DEFAULT_SETTINGS);
			$this->games[$game->getId()] = $game;
		}

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $unused) : void{
			foreach($this->games as $game){
				$game->doTick();
			}
		}), 20);

		$this->getServer()->getCommandMap()->registerAll("amongus", [
			new AmongUsCommand(),
			new AmongUsManageCommand()
		]);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	public function onDisable() : void{
		$data = [];
		foreach($this->games as $game){
			$data[$game->getId()] = $game->jsonSerialize();
		}
		file_put_contents($this->getDataFolder() . "AmongUsData.json", json_encode($data));
	}

	public function getWorldName() : string{
		return $this->getConfig()->get("world_name", "amongus");
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

	public function getNextId() : int{
		$i = 0;
		while(isset($this->games[$i])){
			$i++;
		}
		return $i;
	}

	public function getAvailableGame(Player $player) : ?Game{
		foreach($this->games as $game){
			if($game->canJoin($player)){
				return $game;
			}
		}
		return null;
	}

	public function copyWorld(Game $game, Closure $successCallback) : void{
		if(is_dir($dir = $this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name") . "_{$game->getId()}/")){
			$this->deleteWorld($game, function() use ($game, $successCallback) : void{
				$this->copyWorld($game, $successCallback);
			});
			return;
		}
		$this->getServer()->getAsyncPool()->submitTask(new WorldCopyAsyncTask($this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name") . "/", $this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name") . "_{$game->getId()}/", $successCallback));
	}

	private function deleteWorld(Game $game, Closure $successCallback) : void{
		$this->getServer()->getAsyncPool()->submitTask(new WorldDeleteAsyncTask($this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name") . "_{$game->getId()}/", $successCallback));
	}
}
