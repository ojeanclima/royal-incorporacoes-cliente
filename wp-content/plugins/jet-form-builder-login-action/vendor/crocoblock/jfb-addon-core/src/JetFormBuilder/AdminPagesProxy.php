<?php


namespace JetLoginCore\JetFormBuilder;


abstract class AdminPagesProxy {

	use WithInit;

	abstract public function pages(): array;

	public function plugin_version_compare(): string {
		return '2.0.0';
	}

	public function on_plugin_init() {
		add_filter(
			'jet-form-builder/admin/pages',
			array( $this, 'register_pages' )
		);
	}

	public function register_pages( $pages ) {
		$pages = array_merge( $pages, $this->pages() );

		return $pages;
	}

}