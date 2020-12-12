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

namespace alvin0319\AmongUs\sabotage;

use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\Player;
use alvin0319\AmongUs\form\SabotageForm;
use alvin0319\AmongUs\game\Game;
use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\sabotage\LightsSabotage;

abstract class Sabotage implements Listener{
	/** @var Position */
	protected $pos;
	/** @var Game */
	protected $game;

	public function __construct(Game $game, Position $pos){
		$this->game = $game;
		$this->pos = $pos;
	}

	final public function getPosition() : Position{
		return $this->pos;
	}

	/**
	 * Called when imposters activate sabotage
	 *
	 * @param Player $player
	 */
	abstract public function onActivate(Player $player) : void;

	/**
	 * Called when crewmates or imposters interact sabotage
	 *
	 * @param Player $player
	 */
	abstract public function onInteract(Player $player) : void;

	public function getCool() : int{
		return 5;
	}
}