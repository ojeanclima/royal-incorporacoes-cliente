<?php


namespace DevCoreJFB;


abstract class BaseScript {
	public $config;

	public function setConfig( array $config ): BaseScript {
		$this->config = $config;

		return $this;
	}

	abstract public function slug(): string;

	abstract public function run(): void;


}