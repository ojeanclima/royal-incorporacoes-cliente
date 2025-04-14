<?php


namespace JetLoginCore;


interface FieldModifierIT {

	public function type(): string;

	public function getFormId(): int;

	public function onRender(): array;

	public function getArgs(): array;

	public function getClass();

	public function renderHandler( $args, $instance ): array;

	public function editorAssets();

	public static function register();

}