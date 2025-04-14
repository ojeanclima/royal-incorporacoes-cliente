<?php

namespace DevCoreJFB;

class ScriptsManager {
	private static $scripts = array();

	private static function registerScripts(): void {
		$scripts = array(
			new RenameNamespace()
		);

		foreach ( $scripts as $script ) {
			self::$scripts[ $script->slug() ] = $script;
		}
	}

	public static function issetScript( string $slug ): bool {
		return isset( self::$scripts[ $slug ] );
	}

	public static function getScript( string $slug ): BaseScript {
		return self::$scripts[ $slug ];
	}

	public static function init( $event ) {
		//$repositoryManager = $event->getComposer()->getRepositoryManager();
		$jfbCoreConfig = $event->getComposer()->getConfig()->get('jfb-core');

		if ( ! $jfbCoreConfig ) {
			return;
		}
		self::registerScripts();

		foreach ( $jfbCoreConfig as $scriptSlug => $options ) {
			if ( self::issetScript( $scriptSlug ) ) {
				if ( empty( $options ) && ! is_array( $options ) ) {
					continue;
				}

				self::getScript( $scriptSlug )
				    ->setConfig( $options )
				    ->run();
			}
		}
	}
}