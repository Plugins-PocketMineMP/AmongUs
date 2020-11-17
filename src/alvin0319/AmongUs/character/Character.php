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

namespace alvin0319\AmongUs\character;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\event\PlayerObjectiveCompleteEvent;
use alvin0319\AmongUs\game\Game;
use alvin0319\AmongUs\object\Objective;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

abstract class Character{
	/** @var Player */
	protected $player;
	/** @var Objective[] */
	protected $objectives = [];
	/** @var Objective[] */
	protected $completedObjectives = [];
	/** @var Skin */
	protected $oldSkin = null;

	public function __construct(Player $player){
		$this->player = $player;
	}

	final public function getPlayer() : Player{
		return $this->player;
	}

	abstract public function getName() : string;

	abstract public function getDescription() : string;

	/**
	 * @return Item[]
	 */
	abstract public function getItems() : array;

	public function completeObjective(Objective $objective) : void{
		if(!isset($this->objectives[$objective->getName()])){
			return;
		}
		$ev = new PlayerObjectiveCompleteEvent($this->player, $objective);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->completedObjectives[$objective->getName()] = $objective;
			$this->player->sendMessage(AmongUs::$prefix . "Objective " . $this->getName() . " completed!");
			$this->player->getLevel()->addSound(new GenericSound($this->player, LevelSoundEventPacket::SOUND_LEVELUP), [$player]);
		}
	}

	/**
	 * @param Objective[] $objectives
	 */
	public function setObjectives(array $objectives) : void{
		$this->objectives = $objectives;
	}

	public function isCompletedObjective(Objective $objective) : bool{
		return isset($this->completedObjectives[$objective->getName()]);
	}

	public function hasObjective(Objective $objective) : bool{
		return isset($this->objectives[$objective->getName()]);
	}

	public function start(Game $game) : void{
		$this->oldSkin = clone $this->player->getSkin();
	}
}