const path = require( 'path' );

const WPExtractorPlugin = require(
	'@wordpress/dependency-extraction-webpack-plugin',
);

module.exports = {
	name: 'blocks',
	context: path.resolve( __dirname, 'src' ),
	entry: {
		'admin-controls': '../src/index.js',
		'jfb-action': '../src-jfb/index.js',
		'jfb-action-v2': '../src-jfb-v2/index.js',
	},
	output: {
		path: __dirname,
		filename: 'js/[name].js',
		devtoolNamespace: 'jet-engine-relations',
	},
	devtool: 'source-map',
	resolve: {
		modules: [
			path.resolve( __dirname, 'src' ),
			path.resolve( __dirname, 'src-jfb' ),
			path.resolve( __dirname, 'src-jfb-v2' ),
			'node_modules',
		],
		extensions: [ '.js', '.jsx' ],
		alias: {
			'@': path.resolve( __dirname, 'src' ),
		},
	},
	plugins: [
		new WPExtractorPlugin(),
	],
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