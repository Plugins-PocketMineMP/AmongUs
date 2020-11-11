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
use pocketmine\utils\Utils;
use RecursiveDirectoryIterator;
use SplFileInfo;

use function array_diff;
use function count;
use function rmdir;
use function scandir;
use function substr;
use function unlink;

class WorldDeleteAsyncTask extends AsyncTask{
	/** @var string */
	protected $world;

	public function __construct(string $world, Closure $successCallback){
		$this->world = $world;
		$this->storeLocal($successCallback);
	}

	public function onRun() : void{
		$this->recursiveRmdir($this->world);
	}

	private function recursiveRmdir(string $dir) : void{
		$dir = Utils::cleanPath($dir);
		if(substr($dir, -1) !== "/"){
			$dir .= "/";
		}

		$recursiveDirectoryIterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);

		if(count(array_diff(scandir($dir), [".", ".."])) === 0){
			rmdir($dir);
			return;
		}

		/** @var SplFileInfo */
		foreach($recursiveDirectoryIterator as $fileInfo){
			if($fileInfo->getFilename() !== "." && $fileInfo->getFilename() !== ".."){
				if($fileInfo->isDir()){
					$this->recursiveRmdir($dir . $fileInfo->getFilename());
				}else{
					unlink($dir . $fileInfo->getFilename());
				}
			}
		}

		if(count(array_diff(scandir($dir), [".", ".."])) === 0){
			rmdir($dir);
		}else{
			$this->recursiveRmdir($dir);
		}
	}

	public function onCompletion(Server $server) : void{
		AmongUs::getInstance()->getLogger()->debug("World deletion {$this->world} success.");
		($this->fetchLocal())();
	}
}