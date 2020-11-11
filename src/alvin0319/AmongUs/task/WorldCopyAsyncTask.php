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

namespace alvin0319\AmongUs\task;

use alvin0319\AmongUs\AmongUs;
use Closure;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use RecursiveDirectoryIterator;
use SplFileInfo;

use function copy;
use function is_dir;
use function mkdir;
use function substr;

use const DIRECTORY_SEPARATOR;

class WorldCopyAsyncTask extends AsyncTask{
	/** @var string */
	protected $origin;
	/** @var string */
	protected $destination;

	public function __construct(string $origin, string $destination, Closure $successCallback){
		$this->origin = $origin;
		$this->destination = $destination;
		$this->storeLocal($successCallback);
	}

	public function onRun() : void{
		$this->recursiveCopy($this->origin, $this->destination);
	}

	private function recursiveCopy(string $origin, string $destination) : void{
		if(substr($origin, -1) !== DIRECTORY_SEPARATOR){
			$origin .= DIRECTORY_SEPARATOR;
		}
		if(substr($destination, -1) !== DIRECTORY_SEPARATOR){
			$destination .= DIRECTORY_SEPARATOR;
		}

		$recursiveDirectoryIteratorIterator = new RecursiveDirectoryIterator($origin, RecursiveDirectoryIterator::SKIP_DOTS);

		if(!is_dir($destination))
			mkdir($destination);

		/**
		 * @var SplFileInfo $fileInfo
		 */
		foreach($recursiveDirectoryIteratorIterator as $fileInfo){
			if($fileInfo->getFilename() !== "." && $fileInfo->getFilename() !== ".."){
				if($fileInfo->isDir()){
					$this->recursiveCopy($origin . $fileInfo->getFilename(), $destination . $fileInfo->getFilename());
				}else{
					copy($origin . $fileInfo->getFilename(), $destination . $fileInfo->getFilename());
				}
			}
		}
	}

	public function onCompletion(Server $server) : void{
		AmongUs::getInstance()->getLogger()->debug("World {$this->origin} copied successfully.");
		($this->fetchLocal())();
	}
}