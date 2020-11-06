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

namespace alvin0319\AmongUs\form\game;

use alvin0319\AmongUs\game\Game;
use pocketmine\form\Form;

use pocketmine\Player;

use function array_map;
use function is_int;

class VoteImposterForm implements Form{
	/** @var Game */
	protected $game;

	protected $players = [];

	public function __construct(Game $game){
		$this->game = $game;
	}

	public function jsonSerialize() : array{
		$this->players = $this->game->getPlayers();
		return [
			"type" => "form",
			"title" => "Vote imposter",
			"content" => "Vote imposter!",
			"buttons" => array_map(function(Player $player) : array{
				return ["text" => "Vote to {$player->getName()}"];
			}, $this->players)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		
	}
}