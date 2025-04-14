const path    = require( 'path' );
const webpack = require( 'webpack' );

const WPExtractorPlugin   = require(
	'@wordpress/dependency-extraction-webpack-plugin',
);

module.exports = {
	name: 'js_bundle',
	context: path.resolve( __dirname, 'src' ),
	entry: {
		'blocks.js': './blocks.js',
		'jet-forms.js': './jet-forms.action.js',
		'jet-forms-v2.js': './jet-forms-v2/index.js',
	},
	output: {
		path: path.resolve( __dirname, 'build' ),
		filename: '[name]',
		devtoolNamespace: 'jet-engine-rest-api-listings',
	},
	devtool: 'source-map',
	resolve: {
		modules: [
			path.resolve( __dirname, 'src' ),
			'node_modules',
		],
		extensions: [ '.js', '.jsx' ],
		alias: {
			'@': path.resolve( __dirname, 'src' ),
			'bases': path.resolve( __dirname, 'src/js/bases/' ),
			'filters': path.resolve( __dirname, 'src/js/filters/' ),
			'modules': path.resolve( __dirname, 'src/js/modules/' ),
			'includes': path.resolve( __dirname, 'src/js/includes/' ),
			'blocks': path.resolve( __dirname, 'src/js/blocks/' ),
		},
	},
	plugins: [
		new webpack.ProvidePlugin( {
			jQuery: 'jquery',
			$: 'jquery',
		} ),
		new WPExtractorPlugin(),
	],
	optimization: {
		splitChunks: {
			chunks: 'all',
		},
	},
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
		],
	},
	externalsType: 'window',
	externals: {
		'jet-form-builder-components': [ 'jfb', 'components' ],
		'jet-form-builder-data': [ 'jfb', 'data' ],
		'jet-form-builder-actions': [ 'jfb', 'actions' ],
		'jet-form-builder-blocks-to-actions': [ 'jfb', 'blocksToActions' ],
	},
};