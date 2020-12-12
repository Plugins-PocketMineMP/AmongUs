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

namespace alvin0319\AmongUs\game;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\character\Character;
use alvin0319\AmongUs\character\Crewmate;
use alvin0319\AmongUs\character\Imposter;
use alvin0319\AmongUs\entity\DeadPlayerEntity;
use alvin0319\AmongUs\event\GameEndEvent;
use alvin0319\AmongUs\event\GameStartEvent;
use alvin0319\AmongUs\objective\Objective;
use alvin0319\AmongUs\sabotage\Sabotage;
use alvin0319\AmongUs\task\DisplayTextTask;
use alvin0319\SimpleMapRenderer\item\FilledMap;
use kim\present\lib\arrayutils\ArrayUtils as Arr;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;

use function array_filter;
use function array_map;
use function array_rand;
use function array_search;
use function array_values;
use function arsort;
use function ceil;
use function count;
use function explode;
use function implode;
use function in_array;
use function round;
use function shuffle;
use function time;

class Game{

	public const TEAM_IMPOSTER = "imposter";

	public const TEAM_CREWMATE = "crewmate";

	public const TEAM_NONE = "none";

	public const SETTING_MAX_IMPOSTERS = "max_imposter";

	public const SETTING_MAX_CREW = "max_crew";

	public const SETTING_EMERGENCY_TIME = "emergency_time";

	public const SETTING_EMERGENCY_PRESS = "emergency_press";

	public const SETTING_KILL_COOLDOWN = "kill_cooldown";

	public const SETTING_MIN_PLAYER_TO_START = "min_player_to_start";

	public const SETTING_WAIT_SECOND = "wait_second";

	public const MAP_TYPE_SKELD = "skeld";

	public const MAP_TYPE_POLUS = "polus";

	//public const MAP_TYPE_MIRA_HQ = "mira_hq";

	public const DEFAULT_SETTINGS = [
		self::SETTING_MAX_IMPOSTERS => 2,
		self::SETTING_MAX_CREW => 10,
		self::SETTING_EMERGENCY_TIME => 120, //seconds
		self::SETTING_EMERGENCY_PRESS => 2,
		self::SETTING_KILL_COOLDOWN => 25, //seconds
		self::SETTING_MIN_PLAYER_TO_START => 5,
		self::SETTING_WAIT_SECOND => 30 //seconds
	];

	public const MAP_LIST = [
		self::MAP_TYPE_SKELD,
		self::MAP_TYPE_POLUS
		//self::MAP_TYPE_MIRA_HQ
	];

	/** @var int */
	protected $id;
	/** @var array */
	protected $players = [];
	/** @var int[] */
	protected $settings = self::DEFAULT_SETTINGS;
	/** @var Imposter[] */
	protected $imposters = [];
	/** @var Crewmate[] */
	protected $crews = [];
	/** @var int */
	protected $emergencyTime = self::DEFAULT_SETTINGS[self::SETTING_EMERGENCY_TIME];
	/** @var string */
	protected $map;
	/** @var Position */
	protected $spawnPos;
	/** @var string[] */
	protected $dead = [];
	/** @var int */
	protected $objectiveCount = 0;
	/** @var int */
	protected $objectiveProgress = 0;
	/** @var int[] */
	protected $killCooldowns = [];
	/** @var int */
	protected $waitTick = self::DEFAULT_SETTINGS[self::SETTING_WAIT_SECOND];
	/** @var bool */
	protected $running = false;
	/** @var Objective[] */
	protected $objectives = [];
	/** @var bool */
	protected $emergencyRunning = false;
	/** @var int */
	protected $mapId = -1;
	/** @var int[] */
	protected $votes = [];
	/** @var string[] */
	protected $voteQueue = [];
	/** @var Sabotage[] */
	protected $sabotages = [];
	/** @var Sabotage|null */
	protected $nowSabotage = null;
	/** @var int */
	protected $sabotageCool = -1;
	/** @var string[] */
	protected $rawVents = [];
	/** @var Position[] */
	protected $vents = [];

	public function __construct(int $id, string $map, Position $spawnPos, array $objectives, int $mapId, array $vents = [], array $settings = self::DEFAULT_SETTINGS){
		$this->id = $id;
		$this->map = $map;
		$this->settings = $settings;
		$this->spawnPos = $spawnPos;
		$this->objectives = $objectives;
		$this->mapId = $mapId;
		$this->rawVents = $vents;

		$this->reset();
	}

	private function reset() : void{
		$this->players = [];
		$this->imposters = [];
		$this->crews = [];
		$this->dead = [];
		$this->killCooldowns = [];
		$this->votes = [];
		$this->voteQueue = [];
		$this->objectiveCount = 0;
		$this->objectiveProgress = 0;
		$this->waitTick = $this->settings[self::SETTING_WAIT_SECOND];
		$this->running = false;
		$this->emergencyTime = $this->settings[self::SETTING_EMERGENCY_TIME];
		$this->emergencyRunning = false;
		$this->sabotageCool = -1;

		AmongUs::getInstance()->copyWorld($this, function() : void{
			Server::getInstance()->loadLevel(AmongUs::getInstance()->getWorldName() . "_{$this->getId()}");
			$this->fixPos();
			$this->vents = array_map(function(string $data) : Position{
				[$x, $y, $z] = explode(":", $data);
				return new Position((float) $x, (float) $y, (float) $z, Server::getInstance()->getLevelByName(AmongUs::getInstance()->getWorldName() . "_{$this->getId()}"));
			}, $this->rawVents);
		});
	}

	private function fixPos() : void{
		$this->spawnPos = Position::fromObject($this->spawnPos, Server::getInstance()->getLevelByName(AmongUs::getInstance()->getWorldName() . "_{$this->getId()}"));
	}

	public function getId() : int{
		return $this->id;
	}

	public function getMap() : string{
		return $this->map;
	}

	public function addPlayer(Player $player) : void{
		if(!$this->hasPlayer($player)){
			$this->players[] = $player->getName();
			$this->broadcastMessage("§bGame:§e " . $player->getName() . " has joined the game.");
		}
	}

	public function hasPlayer(Player $player) : bool{
		return in_array($player->getName(), $this->players);
	}

	public function removePlayer(Player $player) : void{
		if($this->hasPlayer($player)){
			unset($this->players[array_search($player->getName(), $this->players)]);
			$this->players = array_values($this->players);
			if(isset($this->imposters[$player->getName()])){
				unset($this->imposters[$player->getName()]);
			}
			if(isset($this->crews[$player->getName()])){
				unset($this->crews[$player->getName()]);
			}
			if(in_array($player->getName(), $this->dead)){
				unset($this->dead[array_search($player->getName(), $this->dead)]);
				$this->dead = array_values($this->dead);
			}
			$this->broadcastMessage("§bGame:§e " . $player->getName() . " has left the game.");
		}
		$this->checkIfGameDone();
	}

	public function broadcastMessage(string $message) : void{
		Server::getInstance()->broadcastMessage(AmongUs::$prefix . $message, $this->getPlayers());
	}

	protected function broadcastPopup(string $popup) : void{
		Server::getInstance()->broadcastTip($popup, $this->getPlayers());
	}

	public function broadcastMessageToDead(string $message) : void{
		Server::getInstance()->broadcastMessage(AmongUs::$prefix . $message, array_values(array_filter($this->getPlayers(), function(Player $player) : bool{
			return $this->isDead($player);
		})));
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers() : array{
		return array_values(array_filter(array_map(function(string $name) : ?Player{
			return Server::getInstance()->getPlayerExact($name);
		}, $this->players), function(?Player $player) : bool{
			return $player !== null;
		}));
	}

	private function shufflePlayers() : void{
		$players = $this->getPlayers();

		shuffle($players);

		foreach($players as $player){
			if(count($this->crews) > count($this->imposters) && count($this->imposters) < $this->settings[self::SETTING_MAX_IMPOSTERS]){
				$this->imposters[$player->getName()] = $character = new Imposter($player);
			}else{
				$this->crews[$player->getName()] = $character = new Crewmate($player);
			}
			$player->sendMessage(AmongUs::$prefix . "§eShhhhh §aRole: §f" . $character->getName() . "§b!");
			$player->teleport($this->spawnPos);
		}

		$imposters = count($this->filterImposters());
		$str = "§bThere are §f{$imposters} §cimposters §bamong us";

		AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, $str, ""), 3);
	}

	public function setMapId(int $mapId) : void{
		$this->mapId = $mapId;
	}

	public function getObjectiveByPos(Position $pos) : ?Objective{
		foreach(array_values($this->objectives) as $objective){
			if($objective->getPosition()->equals($pos)){
				return $objective;
			}
		}
		return null;
	}

	public function canKillPlayer(Player $imposter, Player $crew) : bool{
		if($this->isDead($crew)){
			return false;
		}
		if($this->isDead($imposter)){
			return false;
		}
		if($this->isEmergencyRunning()){
			return false;
		}
		if($this->getCharacter($crew) instanceof Imposter){
			return false;
		}
		if(isset($this->killCooldowns[$imposter->getName()])){
			if(time() - $this->killCooldowns[$imposter->getName()] < $this->settings[self::SETTING_KILL_COOLDOWN]){
				return false;
			}
		}
		return true;
	}

	public function killPlayer(Player $player, ?Player $killer = null) : void{
		$this->dead[] = $player->getName();

		$player->setInvisible(true);
		$player->setGamemode(Player::ADVENTURE);
		$player->setAllowFlight(true);
		$player->setFlying(true);

		if($killer !== null){
			$messages = [
				"You were killed by §c" . $killer->getName() . "§f!",
				"§c" . $killer->getName() . "§fswallowed you! §c",
				"You were shot by §c" . $killer->getName() . "§f!",
				"§c" . $killer->getName() . "§fsnapped your neck" . "§f!",
				"You were killed by §c" . $killer->getName() . "§fusing magic" . "§f!",
				"You were stabbed by §c" . $killer->getName() . "§f!"
			];
			$player->sendTitle("§c§l[ §f! §c]", $messages[array_rand($messages)]);
			$this->killCooldowns[$killer->getName()] = time();
			$nbt = Entity::createBaseNBT($player);
			$nbt->setTag(new CompoundTag("Skin", [
				new StringTag("Name", $player->getSkin()->getSkinId()),
				new ByteArrayTag("Data", $player->getSkin()->getSkinData())
			]));
			$nbt->setString("playerName", $player->getName());
			$entity = Entity::createEntity("DeadPlayerEntity", $player->getLevel(), $nbt);
			$entity->spawnToAll();
		}
		$this->checkIfGameDone();
	}

	private function checkIfGameDone() : void{
		if((int) $this->getProgress() === 100){
			$this->end(self::TEAM_CREWMATE);
			return;
		}
		if(count($this->imposters) >= count($this->filterCrewmates())){
			$this->end(self::TEAM_IMPOSTER);
			return;
		}
		if(count($this->players) <= 1){
			$this->end(self::TEAM_NONE);
		}
	}

	public function isDead(Player $player) : bool{
		return in_array($player->getName(), $this->dead);
	}

	public function getCharacter(Player $player) : ?Character{
		if(!$this->hasPlayer($player)){
			return null;
		}
		return $this->imposters[$player->getName()] ?? $this->crews[$player->getName()] ?? null;
	}

	public function addProgress() : void{
		$this->objectiveProgress += 1;
		$this->checkIfGameDone();
	}

	public function getProgress() : float{
		if($this->objectiveCount === 0 || $this->objectiveProgress === 0){
			return 0.0;
		}
		return (float) (ceil($this->objectiveProgress / $this->objectiveCount) * 100);
	}

	public function getRawProgress() : int{
		return $this->objectiveCount;
	}

	public function isRunning() : bool{
		return $this->running;
	}

	public function isEmergencyRunning() : bool{
		return $this->emergencyRunning;
	}

	public function onEmergencyCall(Player $who, ?DeadPlayerEntity $entity) : void{
		$this->emergencyRunning = true;
		$this->broadcastMessage("§aEmergency Meeting§c!§r (§bcaller: {$who->getName()})");
		if($entity !== null){
			$this->broadcastMessage($entity->getPlayerName() . " is dead!");
		}
		foreach($this->getPlayers() as $player){
			$player->teleport($this->spawnPos);
		}
		foreach($this->getPlayers() as $player){
			$this->votes[$player->getName()] = 0;
		}
		$this->votes["skip"] = 0;
	}

	public function votePlayer(Player $player, string $target) : void{
		if(in_array($player->getName(), $this->dead)){
			$player->sendMessage(AmongUs::$prefix . "You cannot vote at this time.");
			return;
		}
		if(in_array($player->getName(), $this->voteQueue)){
			$player->sendMessage(AmongUs::$prefix . "You have already voted.");
			return;
		}
		$this->voteQueue[] = $player->getName();
		$this->votes[$target] += 1;

		$player->sendMessage(AmongUs::$prefix . "You've voted for " . $target);
		$this->checkVote();
	}

	public function addVent(Position $pos) : void{
		$this->vents[] = $pos;
		if($this->isRunning()){
			$this->spawnVent($pos);
		}
	}

	public function spawnVent(Position $pos) : void{
		$skin = AmongUs::getInstance()->getVentSkin();
		if(!$pos->getLevel()->isChunkLoaded($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4)){
			$pos->getLevel()->loadChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);
		}
		$nbt = Entity::createBaseNBT($pos);
		$nbt->setTag(new CompoundTag("Skin", [
			new StringTag("Name", $skin->getSkinId()),
			new ByteArrayTag("Data", $skin->getSkinData()),
			new ByteArrayTag("CapeData", ""),
			new StringTag("GeometryName", $skin->getGeometryName()),
			new ByteArrayTag("GeometryData", $skin->getGeometryData())
		]));
		$entity = Entity::createEntity("Vent", $pos->getLevel(), $nbt);
		$entity->spawnToAll();
	}

	public function getAvailableVents(Vector3 $pos) : array{
		$res = [];
		foreach($this->vents as $vent){
			$posStr = implode(":", [$vent->getX(), $vent->getY(), $vent->getZ()]);
			$res[$posStr] = round($pos->distance($vent), 2);
		}
		arsort($res);

		return Arr::sliceFrom($res, 0, 3, true)->map(function($distance, string $posStr, array $unused) : Position{
			[$x, $y, $z] = explode(":", $posStr);
			return new Position((float) $x, (float) $y, (float) $z, $this->spawnPos->getLevel());
		})->valuesAs();
	}

	public function endEmergencyTime() : void{
		$this->emergencyRunning = false;
		$this->emergencyTime = $this->settings[self::SETTING_EMERGENCY_TIME];

		arsort($this->votes);

		$max = 0;
		$topVote = "";

		$duplicate = false;

		foreach($this->votes as $name => $vote){
			if($vote >= $max){
				if($vote === $max && $name !== $topVote){
					$duplicate = true;
				}
				$max = $vote;
				$topVote = $name;
			}
		}

		if($this->votes["skip"] >= $max){
			$duplicate = true;
		}

		if($duplicate){
			AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, "No one ejected (skipped)", "There are " . count($this->filterImposters()) . " impostors left"), 3);
		}else{
			$character = $this->imposters[$topVote] ?? $this->crews[$topVote] ?? null;
			if($character === null){
				AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, "No one ejected (skipped)", count($this->filterImposters()) . " impostors remaining"), 3);
			}else{
				AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, "{$topVote} was " . ($character instanceof Imposter ? "was the" : "not the") . " impostors", count($this->filterImposters()) . " remaining"), 3);
				$this->killPlayer(AmongUs::getInstance()->getServer()->getPlayerExact($topVote));
			}
		}

		$this->votes = [];
		$this->voteQueue = [];
	}

	private function checkVote() : void{
		if(count($this->voteQueue) === count($this->filterCrewmates() + $this->filterImposters())){
			$this->endEmergencyTime();
		}
	}

	public function canJoin(Player $player) : bool{
		if($this->running){
			return false;
		}
		if(count($this->players) > $this->settings[self::SETTING_MAX_IMPOSTERS] + $this->settings[self::SETTING_MAX_CREW]){
			return false;
		}
		if($this->hasPlayer($player)){
			return false;
		}
		return true;
	}

	/**
	 * @return Imposter[]
	 */
	public function filterImposters() : array{
		return Arr::mapFrom($this->getPlayers(), function(Player $player) : ?Character{
			return $this->getCharacter($player);
		})->filterAs(function(?Character $character) : bool{
			return $character instanceof Imposter;
		});
	}

	/**
	 * @return Crewmate[]
	 */
	public function filterCrewmates() : array{
		return Arr::mapFrom($this->getPlayers(), function(Player $player) : ?Character{
			return $this->getCharacter($player);
		})->filter(function(?Character $character) : bool{
			return $character instanceof Crewmate;
		})->filterAs(function(Crewmate $crewmate) : bool{
			return !$this->isDead($crewmate->getPlayer());
		});
	}

	/**
	 * @return Player[]
	 */
	public function getCrewmates() : array{
		return Arr::mapFrom(Arr::valuesFromAs($this->crews), function(Crewmate $crew) : Player{
			return $crew->getPlayer();
		})->filterAs(function(Player $player) : bool{
			return $player->isOnline();
		});
	}

	/**
	 * @return Player[]
	 */
	public function getImposters() : array{
		return Arr::mapFrom(Arr::valuesFromAs($this->imposters), function(Imposter $imposter) : Player{
			return $imposter->getPlayer();
		})->filterAs(function(Player $player) : bool{
			return $player->isOnline();
		});
	}

	public function spawnVents() : void{
		foreach($this->vents as $position){
			$this->spawnVent($position);
		}
	}

	public function onSabotageActivate(Sabotage $sabotage) : void{
	}

	///////////////////////////////////////////
	//////////// INTERNAL METHODS /////////////
	///////////////////////////////////////////

	/**
	 * Called every 1 seconds
	 *
	 * @internal
	 */
	public function doTick() : void{
		if($this->running){
			if($this->emergencyRunning){
				if(--$this->emergencyTime < 1){
					$this->endEmergencyTime();
				}else{
					$text = "§b§l[§cAmong§bUs]§r§7\n";
					$text .= "Vote ends in §d" . $this->emergencyTime . "§r§7s";
					$this->broadcastPopup($text);
				}
			}
		}else{
			if(count($this->players) >= $this->settings[self::SETTING_MIN_PLAYER_TO_START]){
				if(--$this->waitTick < 1){
					$this->start();
					$this->waitTick = $this->settings[self::SETTING_WAIT_SECOND];
				}else{
					$text = "§b§l[§cAmong§bUs]§r§7\n";
					$text .= "Starts in §d" . $this->waitTick . "§r§7s";
					$this->broadcastPopup($text);
				}
			}else{
				$text = "§b§l[§cAmong§bUs]§r§7\n";
				$text .= "Waiting for more players...\n";
				$text .= "need §d" . ($this->settings[self::SETTING_MIN_PLAYER_TO_START] - count($this->players)) . "§r§7 more players to start";
				$this->broadcastPopup($text);
			}
		}
	}

	private function start() : void{
		$this->running = true;
		$this->shufflePlayers();
		$this->assignObjective();
		$this->spawnVents();
		(new GameStartEvent($this))->call();

		foreach($this->getPlayers() as $player){
			$player->teleport($this->spawnPos);
			$player->getInventory()->clearAll();
			$player->setGamemode(Player::SURVIVAL);
		}
		$this->giveDefaultKits();

		$this->spawnVents();
	}

	private function end(string $winner) : void{
		$this->broadcastMessage(($winner === self::TEAM_NONE ? "Draw" : $winner . " won!"));
		(new GameEndEvent($this, $winner))->call();
		foreach($this->getPlayers() as $player){
			$player->teleport($player->getServer()->getDefaultLevel()->getSafeSpawn());
			$player->getInventory()->clearAll();
			$player->setGamemode(Player::SURVIVAL);
			$player->setInvisible(false);
			$player->setImmobile(false);
		}
		$this->reset();
	}

	private function assignObjective() : void{
		/** @var Objective[] $objectives */
		$objectives = [];
		foreach($this->crews as $name => $crew){
			$objectiveCount = 0;
		}
	}

	private function hasObjectiveTaken(Objective $objective) : bool{
		foreach($this->crews as $name => $crew){
			if($crew->hasObjective($objective)){
				return true;
			}
		}
		return false;
	}

	private function giveDefaultKits() : void{
		if($this->mapId !== -1){
			/** @var FilledMap $map */
			$map = ItemFactory::get(ItemIds::FILLED_MAP, 0, 1);
			$map->setMapId($this->mapId);
			foreach($this->getPlayers() as $player){
				$player->getInventory()->addItem($map);
			}
		}
		foreach($this->getPlayers() as $player){
			$player->getInventory()->addItem(ItemFactory::get(ItemIds::CLOCK, 10, 1)->setCustomName("Vote"));
			$character = $this->getCharacter($player);
			if($character !== null){
				$player->getInventory()->addItem(...$character->getItems());
			}
		}
	}

	public function jsonSerialize() : array{
		$objectives = [];
		foreach($this->objectives as $name => $objective){
			$objectives[$objective->getName()] = implode(":", [
				$objective->getPosition()->getX(),
				$objective->getPosition()->getY(),
				$objective->getPosition()->getZ(),
				$objective->getPosition()->getLevel()->getFolderName()
			]);
		}
		return [
			"settings" => $this->settings,
			"map" => $this->map,
			"spawnPos" => implode(":", [
				$this->spawnPos->getX(),
				$this->spawnPos->getY(),
				$this->spawnPos->getZ(),
				$this->spawnPos->getLevelNonNull()->getFolderName()
			]),
			"objectives" => $objectives,
			"mapId" => $this->mapId,
			"vents" => array_map(function(Position $pos) : string{
				return implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()]);
			}, $this->vents)
		];
	}
}
