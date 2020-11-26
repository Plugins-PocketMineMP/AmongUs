<?php

declare(strict_types=1);

namespace alvin0319\AmongUs\entity;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;

class VentEntity extends Human{

	public function attack(EntityDamageEvent $source) : void{
		$source->setCancelled();
	}
}