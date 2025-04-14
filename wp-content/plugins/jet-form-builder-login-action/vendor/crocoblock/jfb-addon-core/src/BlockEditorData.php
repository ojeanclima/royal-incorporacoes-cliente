<?php


namespace JetLoginCore;


trait BlockEditorData {

	abstract public function editor_data(): array;

	abstract public function editor_labels(): array;

	abstract public function editor_help(): array;

}