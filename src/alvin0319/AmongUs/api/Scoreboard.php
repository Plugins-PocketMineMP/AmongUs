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

namespace alvin0319\AmongUs\api;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

class Scoreboard{
	/** @var Player */
	protected $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function sendLine(string $title, array $lines) : void{
		$this->removeScoreBoard();

		$pk = new SetDisplayObjectivePacket();
		$pk->objectiveName = $this->player->getName();
		$pk->displayName = $title;
		$pk->sortOrder = 0;
		$pk->criteriaName = $this->player->getName();
		$pk->displaySlot = "dummy";
		$this->player->sendDataPacket($pk);

		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_CHANGE;

		foreach($lines as $index => $line){
			$entry = new ScorePacketEntry();
			$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
			$entry->objectiveName = $this->player->getName();
			$entry->score = $index;
			$entry->customName = $line;
			$entry->scoreboardId = $index;
			$pk->entries[] = $entry;
		}
		$this->player->sendDataPacket($pk);
	}

	public function removeScoreBoard() : void{
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->player->getName();
		$this->player->sendDataPacket($pk);
	}
}