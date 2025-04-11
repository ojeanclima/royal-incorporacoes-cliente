<?php

namespace JFB_PDF_Modules\Templates\Blocks;

use JFB_PDF\Plugin;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\Templates\Blocks\Conditional\BlockRender;
use JFB_PDF_Modules\Templates\Blocks\Interfaces\BlockInterface;
use JFB_PDF_Modules\Templates\PostType\PostType;
use JFB_PDF_Modules\Templates\ConditionValidators;

class Manager {

	const ALLOWED_BLOCKS = array(
		'core/paragraph',
		'core/heading',
		'core/image',
		'core/columns',
		'core/column',
		'core/html',
		'core/table',
	);

	private $plugin;
	private $conditions_manager;
	private $registered_blocks = array();

	public function __construct(
		Plugin $plugin,
		CoreColumns $columns,
		CoreColumn $column,
		ConditionValidators\Manager $conditions_manager
	) {
		$this->plugin             = $plugin;
		$this->conditions_manager = $conditions_manager;
	}

	public function init_hooks() {
		add_action(
			'init',
			array( $this, 'init_blocks' )
		);

		add_filter(
			'allowed_block_types_all',
			array( $this, 'unregister_core_blocks' ),
			10,
			2
		);

		// used for our Conditional Block
		add_filter(
			'pre_render_block',
			array( $this, 'filter_conditional_content' ),
			10,
			2
		);
	}

	/**
	 * @return void
	 * @throws InjectionException
	 */
	public function init_blocks() {
		$this->register( Conditional\BlockRender::class );
	}

	/**
	 * @throws InjectionException
	 */
	protected function register( string $block_class ) {
		/** @var BlockInterface $block */
		$block = $this->plugin->get_injector()->make( $block_class );

		$block_type = register_block_type(
			$block->get_block_json(),
			array(
				'render_callback' => $this->plugin->get_injector()->buildExecutable(
					sprintf( '%s::render', $block_class )
				),
			)
		);

		$this->registered_blocks[] = $block_type->name;
	}

	/**
	 * @param $content
	 * @param array $parsed_block
	 *
	 * @return mixed|string
	 * @throws InjectionException
	 */
	public function filter_conditional_content( $content, array $parsed_block ) {
		if ( BlockRender::NAME !== $parsed_block['blockName'] ) {
			return $content;
		}

		$func_type = ( $parsed_block['attrs']['funcType'] ?? '' ) ?: 'show';

		if ( ! $this->conditions_manager->validate(
			$parsed_block['attrs']['conditions'] ?? array()
		) ) {
			return 'show' === $func_type ? '' : $content;
		}

		return 'hide' === $func_type ? '' : $content;
	}

	/**
	 * @param bool|string[] $allowed_block_types
	 * @param \WP_Block_Editor_Context $editor_context
	 *
	 * @return bool|string[]
	 */
	public function unregister_core_blocks(
		$allowed_block_types,
		$editor_context
	) {
		if ( ! is_a( $editor_context, 'WP_Block_Editor_Context' ) ) {
			return $allowed_block_types;
		}

		if (
			'core/edit-post' !== $editor_context->name ||
			PostType::SLUG !== $editor_context->post->post_type
		) {
			return $allowed_block_types;
		}

		return array_merge(
			self::ALLOWED_BLOCKS,
			$this->registered_blocks
		);
	}


}
