<?php

declare(strict_types=1);

namespace alvin0319\AmongUs\object;

use alvin0319\AmongUs\AmongUs;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\item\ItemIds;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

use function in_array;

class EnergyChangeObjective extends Objective{

	public function getName() : string{
		return "Energy change";
	}

	public function onInteract(Player $player) : void{
		$game = AmongUs::getInstance()->getGameByPlayer($player);
		if($game === null){
			return;
		}
		$character = $game->getCharacter($player);
		if($character === null){
			return;
		}
		if($character->isCompletedObjective($this)){
			return;
		}
		if(!$character->hasObjective($this)){
			return;
		}
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName("Energy change");

		$slots = [
			2,
			3,
			4,
			5,
			6,
			11,
			12,
			13,
			14,
			15,
			20,
			21,
			22,
			23,
			24
		];

		$menu->setListener(function(InvMenuTransaction $action) use ($menu, $slots, $game, $character) : InvMenuTransactionResult{
			$player = $action->getPlayer();

			if(!in_array($action->getAction()->getSlot(), $slots)){
				$player->getCursorInventory()->sendSlot(0, $player);
				return $action->discard();
			}

			$player->getCursorInventory()->sendSlot(0, $player);

			$valid = true;

			for($i = 11; $i < 16; $i++){
				if($menu->getInventory()->getItem($i)->getId() !== ItemIds::EMERALD){
					$valid = false;
				}
			}
			if($valid){
				$menu->getInventory()->onClose($player);
				$character->completeObjective($this);

				$game->addProgress();

				return $action->continue()->then(function(Player $o) : void{
					$o->getCursorInventory()->clearAll();
				});
			}

			return $action->continue();
		});
	}
}