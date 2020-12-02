<?php

declare(strict_types=1);

namespace alvin0319\AmongUs\entity;

use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\character\Imposter;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class VentEntity extends Human{

	protected $ventTick = 20 * 3; // 3 seconds

	protected function initEntity() : void{
		parent::initEntity();
		$this->setCanSaveWithChunk(false); // DO NOT SAVE ME!!!!!!!
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->setCancelled();
	}

	public function useVent(Player $player) : void{
		$game = AmongUs::getInstance()->getGameByPlayer($player);
		if($game === null){
			return;
		}
		$character = $game->getCharacter($player);
		if(!$character instanceof Imposter){
			return;
		}

		$this->ventTick = 20 * 3;

		$this->setSkin(AmongUs::getInstance()->getOpenVentSkin());
		$this->sendSkin();

		$player->setInvisible(true);
		$player->setImmobile(true);
	}

	public function onUpdate(int $currentTick) : bool{
		$parent = parent::onUpdate($currentTick);
		if($this->ventTick !== 0){
			--$this->ventTick;
			if($this->ventTick <= 0){
				$this->setSkin(AmongUs::getInstance()->getVentSkin());
				$this->sendSkin();
			}
		}
		return $parent;
	}
}