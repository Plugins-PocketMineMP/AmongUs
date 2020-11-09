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

namespace alvin0319\AmongUs\libs\muqsit\invmenu;

use InvalidArgumentException;
use alvin0319\AmongUs\AmongUs;
use alvin0319\AmongUs\libs\muqsit\invmenu\metadata\DoubleBlockMenuMetadata;
use alvin0319\AmongUs\libs\muqsit\invmenu\metadata\MenuMetadata;
use alvin0319\AmongUs\libs\muqsit\invmenu\metadata\SingleBlockMenuMetadata;
use alvin0319\AmongUs\libs\muqsit\invmenu\session\network\handler\PlayerNetworkHandlerRegistry;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\plugin\Plugin;
use pocketmine\tile\Tile;

final class InvMenuHandler{

	/** @var Plugin|null */
	private static $registrant;

	/** @var MenuMetadata[] */
	private static $menu_types = [];

	public static function getRegistrant() : ?Plugin{
		return self::$registrant;
	}

	public static function register(Plugin $plugin) : void{
		if(self::isRegistered()){
			throw new InvalidArgumentException("{$plugin->getName()} attempted to register " . self::class . " twice.");
		}

		self::$registrant = $plugin;
		self::registerDefaultMenuTypes();
		PlayerNetworkHandlerRegistry::init();
		$plugin->getServer()->getPluginManager()->registerEvents(new InvMenuEventHandler($plugin), $plugin);
	}

	public static function isRegistered() : bool{
		return self::$registrant instanceof Plugin;
	}

	private static function registerDefaultMenuTypes() : void{
		self::registerMenuType(new SingleBlockMenuMetadata(InvMenu::TYPE_CHEST, 27, WindowTypes::CONTAINER, BlockFactory::get(BlockIds::CHEST), Tile::CHEST));
		self::registerMenuType(new DoubleBlockMenuMetadata(InvMenu::TYPE_DOUBLE_CHEST, 54, WindowTypes::CONTAINER, BlockFactory::get(BlockIds::CHEST), Tile::CHEST));
		self::registerMenuType(new SingleBlockMenuMetadata(InvMenu::TYPE_HOPPER, 5, WindowTypes::HOPPER, BlockFactory::get(BlockIds::HOPPER_BLOCK), "Hopper"));
	}

	public static function registerMenuType(MenuMetadata $type, bool $override = false) : void{
		if(isset(self::$menu_types[$identifier = $type->getIdentifier()]) && !$override){
			throw new InvalidArgumentException("A menu type with the identifier \"{$identifier}\" is already registered as " . get_class(self::$menu_types[$identifier]));
		}

		self::$menu_types[$identifier] = $type;
	}

	public static function getMenuType(string $identifier) : ?MenuMetadata{
		return self::$menu_types[$identifier] ?? null;
	}
}