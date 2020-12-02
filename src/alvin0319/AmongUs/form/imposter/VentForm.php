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

namespace alvin0319\AmongUs\form\imposter;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\game\Game;
use pocketmine\form\Form;
use pocketmine\level\Position;
use pocketmine\Player;

use function array_map;
use function array_merge;
use function is_int;

class VentForm implements Form{
	/** @var Player */
	protected $player;
	/** @var Position[] */
	protected $vents = [];
	/** @var Game */
	protected $game;

	public function __construct(Player $player){
		$this->player = $player;
		$this->game = AmongUs::getInstance()->getGameByPlayer($player);
	}

	public function jsonSerialize() : array{
		$this->vents = $this->game->getAvailableVents($this->player);
		return [
			"type" => "form",
			"title" => "Select the vent what you want to teleport",
			"content" => "",
			"buttons" => array_merge([["text" => "Exit"]], array_map(function(Position $pos) : array{
				return ["text" => "{$pos->getX()}:{$pos->getY()}:{$pos->getZ()}"];
			}, $this->vents))
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		if($data === 0 || !isset($this->vents[$data])){
			$player->setInvisible(false);
			$player->setImmobile(false);
			return;
		}
		$player->teleport($this->vents[$data]);
		$player->sendForm(new self($player)); // re-initialize
	}
}