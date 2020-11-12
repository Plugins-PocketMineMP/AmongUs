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
use alvin0319\AmongUs\item\FilledMap;
use alvin0319\AmongUs\object\Objective;
use alvin0319\AmongUs\sabotage\Sabotage;
use alvin0319\AmongUs\task\DisplayTextTask;
use blugin\utils\arrays\ArrayUtil as Arr;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;

use function array_filter;
use function array_map;
use function array_search;
use function array_values;
use function arsort;
use function ceil;
use function count;
use function implode;
use function in_array;
use function shuffle;
use function time;

class Game{

	public const TEAM_IMPOSTER = "imposter";

	public const TEAM_CREWMATE = "crewmate";

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
	/** @var FilledMap|null */
	protected $mapItem = null;
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

	public function __construct(int $id, string $map, Position $spawnPos, array $objectives, ?FilledMap $mapItem = null, array $settings = self::DEFAULT_SETTINGS){
		$this->id = $id;
		$this->map = $map;
		$this->settings = $settings;
		$this->spawnPos = $spawnPos;
		$this->objectives = $objectives;
		if($mapItem !== null){
			$this->mapItem = clone $mapItem;
		}

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

	public function getMapItem() : FilledMap{
		return clone $this->mapItem;
	}

	public function setMapItem(FilledMap $map) : void{
		$this->mapItem = clone $map;
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
		Server::getInstance()->broadcastPopup($popup, $this->getPlayers());
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
		$str = "§bThere are §f{$imposters} §cimposters §bbetween us";

		AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, $str, ""), 3);
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
			$player->sendTitle("§c§l[ §f! §c]", "You've been killed by " . $killer->getName() . "!");
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
			$player->sendMessage(AmongUs::$prefix . "You've been already voted.");
			return;
		}
		$this->voteQueue[] = $player->getName();
		$this->votes[$target] += 1;

		$player->sendMessage(AmongUs::$prefix . "You've voted out " . $target);
		$this->checkVote();
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
			AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, "No one was thrown (skipped vote)", "There are " . count($this->filterImposters()) . " imposters left"), 3);
		}else{
			$character = $this->imposters[$topVote] ?? $this->crews[$topVote] ?? null;
			if($character === null){
				AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, "No one was thrown (skipped vote)", "There are " . count($this->filterImposters()) . " imposters left"), 3);
			}else{
				AmongUs::getInstance()->getScheduler()->scheduleRepeatingTask(new DisplayTextTask($this, "{$topVote} was " . ($character instanceof Imposter ? "was the" : "not the") . " imposter", "There are " . count($this->filterImposters()) . " imposters left"), 3);
				$this->killPlayer(AmongUs::getInstance()->getServer()->getPlayerExact($topVote));
			}
		}

		$this->votes = [];
		$this->voteQueue = [];
	}

	private function checkVote() : void{
		if(count($this->voteQueue) === count($this->filterCrewmates())){
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

	public function filterImposters() : array{
		return Arr::map($this->getPlayers(), function(Player $player) : ?Character{
			return $this->getCharacter($player);
		})->filter(function(?Character $character) : bool{
			return $character instanceof Imposter;
		})->toArray();
	}

	public function filterCrewmates() : array{
		return Arr::map($this->getPlayers(), function(Player $player) : ?Character{
			return $this->getCharacter($player);
		})->filter(function(?Character $character) : bool{
			return $character instanceof Crewmate;
		})->filter(function(Crewmate $crewmate) : bool{
			return !$this->isDead($crewmate->getPlayer());
		})->values()->toArray();
	}

	public function getCrewmates() : array{
		return array_values(array_filter(array_map(function(Crewmate $crew) : Player{
			return $crew->getPlayer();
		}, array_values($this->crews)), function(Player $player) : bool{
			return $player->isOnline();
		}));
	}

	public function getImposters() : array{
		return array_values(array_filter(array_map(function(Imposter $imposter) : Player{
			return $imposter->getPlayer();
		}, array_values($this->imposters)), function(Player $player) : bool{
			return $player->isOnline();
		}));
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
		(new GameStartEvent($this))->call();

		foreach($this->getPlayers() as $player){
			$player->teleport($this->spawnPos);
			$player->getInventory()->clearAll();
			$player->setGamemode(Player::SURVIVAL);
		}
		$this->giveDefaultKits();
	}

	private function end(string $winner) : void{
		$this->broadcastMessage($winner . " win!");
		(new GameEndEvent($this, $winner))->call();
		foreach($this->getPlayers() as $player){
			$player->teleport($player->getServer()->getDefaultLevel()->getSafeSpawn());
			$player->getInventory()->clearAll();
			$player->setGamemode(Player::SURVIVAL);
			$player->setInvisible(false);
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
		if($this->mapItem !== null){
			foreach($this->getPlayers() as $player){
				$player->getInventory()->addItem(clone $this->mapItem);
			}
		}
		foreach($this->getPlayers() as $player){
			$player->getInventory()->addItem(ItemFactory::get(ItemIds::CLOCK, 0, 1)->setCustomName("Vote"));
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
		$data = [
			"settings" => $this->settings,
			"map" => $this->map,
			"spawnPos" => implode(":", [
				$this->spawnPos->getX(),
				$this->spawnPos->getY(),
				$this->spawnPos->getZ(),
				$this->spawnPos->getLevel()->getFolderName()
			]),
			"objectives" => $objectives
		];
		if($this->mapItem !== null){
			$data["mapItem"] = $this->mapItem->jsonSerialize();
		}
		return $data;
	}
}
