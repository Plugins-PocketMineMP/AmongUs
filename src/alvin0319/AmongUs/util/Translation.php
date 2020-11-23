<?php

declare(strict_types=1);

namespace alvin0319\AmongUs\util;

use alvin0319\AmongUs\AmongUs;
use pocketmine\utils\SingletonTrait;

use function count;
use function str_replace;

final class Translation{
	use SingletonTrait;

	/** @var AmongUs */
	protected $plugin;
	/** @var array */
	protected $data = [];
	/** @var string */
	protected $locale;

	public function __construct(AmongUs $plugin){
		$this->plugin = $plugin;
		self::setInstance($this);
	}

	public function init() : void{
		self::$instance = $this;
	}

	public function translate(string $key, array $params = []){
		$translated = $this->data[$key] ?? "";
		if(count($params) > 0){
			foreach($params as $key => $value){
				$translated = str_replace("{%{$key}}", $value, $translated);
			}
		}
		return $translated;
	}
}