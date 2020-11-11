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

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\Player;

class Bossbar{
	/** @var Player */
	protected $player;

	protected $eid = -1;

	public function __construct(Player $player){
		$this->player = $player;
		$this->eid = Entity::$entityCount++;
	}

	public function send(string $text) : void{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_SHOW;
		$pk->title = $text;
		$pk->healthPercent = 1;
		$pk->bossEid = $this->eid;
		$this->player->sendDataPacket($pk);
	}

	public function updateText(string $text) : void{
		$this->remove();

		$this->send($text);
	}

	public function remove() : void{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_HIDE;
		$pk->bossEid = $this->eid;
		$this->player->sendDataPacket($pk);
	}
}