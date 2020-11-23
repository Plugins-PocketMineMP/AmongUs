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
use alvin0319\AmongUs\object\Objective;
use alvin0319\AmongUs\task\WorldCopyAsyncTask;
use alvin0319\AmongUs\task\WorldDeleteAsyncTask;
use alvin0319\SimpleMapRenderer\SimpleMapRenderer;
use Closure;
use kim\present\lib\arrayutils\ArrayUtils;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

use function array_keys;
use function array_map;
use function array_values;
use function class_exists;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_dir;
use function json_decode;
use function json_encode;

class AmongUs extends PluginBase{
	use SingletonTrait;

	/** @var string */
	public static $prefix = "§a§l[§cAmong§eUs§a] §r>§7 ";
	/** @var Game[] */
	protected $games = [];
	/** @var Objective[][] */
	protected $objectives = [];

	protected $data = [];

	public const REQUIRED_CLASSES = [
		ArrayUtils::class => "virion",
		InvMenu::class => "virion",
		SimpleMapRenderer::class => "plugin"
	];

	public function onLoad() : void{
		self::$instance = $this;
	}

	public function onEnable() : void{
		if($this->detectBadSpoon()){
			$this->getLogger()->critical("Forks of PocketMine-MP have been detected.");
			$this->getLogger()->critical("This plugin does not work with other software. (e.g Altay)");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->saveDefaultConfig();
		if(!is_dir($this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name", "amongus"))){
			$this->getLogger()->critical("The world set in config was not loaded or couldn't be found.");
		}
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}

		Entity::registerEntity(DeadPlayerEntity::class, true, ["DeadPlayerEntity"]);

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

			$game = new Game($i, $gameData["map"], new Position((float) $x, (float) $y, (float) $z, $this->getServer()->getLevelByName($world)), $objectives, $gameData["settings"] ?? Game::DEFAULT_SETTINGS);
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

	private function detectBadSpoon() : bool{
		return $this->getServer()->getName() !== "PocketMine-MP" || class_exists("\\pocketmine\\maps\\MapData");
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
		if(is_dir($dir = $this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name") . "_{$game->getId()}/")){
			$this->getServer()->getAsyncPool()->submitTask(new WorldDeleteAsyncTask($this->getServer()->getDataPath() . "worlds/" . $this->getConfig()->get("world_name") . "_{$game->getId()}/", $successCallback));
		}
	}
}
