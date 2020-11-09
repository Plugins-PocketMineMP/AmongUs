<?php

/*
 *  ___            __  __
 * |_ _|_ ____   _|  \/  | ___ _ __  _   _
 *  | || '_ \ \ / / |\/| |/ _ \ '_ \| | | |
 *  | || | | \ V /| |  | |  __/ | | | |_| |
 * |___|_| |_|\_/ |_|  |_|\___|_| |_|\__,_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Muqsit
 * @link http://github.com/Muqsit
 *
*/

declare(strict_types=1);

namespace alvin0319\AmongUs\libs\muqsit\invmenu\metadata;

use alvin0319\AmongUs\libs\muqsit\invmenu\session\MenuExtradata;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class DoubleBlockMenuMetadata extends SingleBlockMenuMetadata{

	protected function getBlockActorDataAt(Vector3 $pos, ?string $name) : CompoundTag{
		$tag = parent::getBlockActorDataAt($pos, $name);
		$tag->setInt("pairx", $pos->x + (($pos->x & 1) ? 1 : -1));
		$tag->setInt("pairz", $pos->z);
		return $tag;
	}

	protected function getBlockPositions(MenuExtradata $metadata) : array{
		$pos = $metadata->getPositionNotNull();
		return $pos->y >= 0 && $pos->y < Level::Y_MAX ? [$pos, ($pos->x & 1) ? $pos->east() : $pos->west()] : [];
	}

	protected function calculateGraphicOffset(Player $player) : Vector3{
		$offset = parent::calculateGraphicOffset($player);
		$offset->x *= 2;
		$offset->z *= 2;
		return $offset;
	}
}