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
use alvin0319\AmongUs\AmongUs;
use pocketmine\form\Form;
use pocketmine\Player;

use function is_int;

class VoteImposterForm implements Form{
	/** @var Game */
	protected $game;
	/** @var Player[] */
	protected $players = [];

	public function __construct(Game $game){
		$this->game = $game;
	}

	public function jsonSerialize() : array{
		$this->players = $this->game->getPlayers();
		$buttons = [["text" => "Skip"]];
		foreach($this->players as $player){
			$buttons[] = ["text" => "Vote out {$player->getName()}"];
		}
		return [
			"type" => "form",
			"title" => "Who is the imposter?",
			"content" => "Once you voted, you cannot vote again.",
			"buttons" => $buttons
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		if($data === 0){
			$this->game->votePlayer($player, "skip");
			return;
		}
		$this->game->votePlayer($player, $this->players[$data - 1]->getName());
	}
}
