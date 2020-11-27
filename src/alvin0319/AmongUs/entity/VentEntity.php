<?php

declare(strict_types=1);

namespace alvin0319\AmongUs\entity;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;

class VentEntity extends Human{

	protected function initEntity() : void{
		parent::initEntity();
		$this->setCanSaveWithChunk(false); // DO NOT SAVE ME!!!!!!!
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->setCancelled();
	}
}