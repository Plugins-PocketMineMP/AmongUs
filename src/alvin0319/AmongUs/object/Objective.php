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

namespace alvin0319\AmongUs\object;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

use function explode;
use function in_array;

abstract class Objective{
	/** @var Position */
	protected $pos;

	public function __construct(Position $pos){
		$this->pos = $pos;
	}

	final public function getPosition() : Position{
		return $this->pos;
	}

	abstract public function getName() : string;

	abstract public function onInteract(Player $player) : void;

	public static function getByName(string $name, string $posData) : ?Objective{
		[$x, $y, $z, $world] = explode(":", $posData);
		$pos = new Position((float) $x, (float) $y, (float) $z, Server::getInstance()->getLevelByName($world));
		switch($name){
			case "Energy change":
				return new EnergyChangeObjective($pos);
			case "File receive":
				return new FileReceiveObjective($pos);
			case "File send":
				return new FileSendObjective($pos);
			case "Manifold open":
				return new ManifoldOpenObjective($pos);
			default:
				return null;
		}
	}

	public static function hasObjective(string $name) : bool{
		$objectives = [
			"Energy change",
			"File receive",
			"File send",
			"Manifold open"
		];
		return in_array($name, $objectives);
	}
}