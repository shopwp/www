import path from 'path'
import { fileURLToPath } from 'url'
import ProgressBarPlugin from 'progress-bar-webpack-plugin'
import MiniCssExtractPlugin from 'mini-css-extract-plugin'
import BrowserSyncPlugin from 'browser-sync-webpack-plugin'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const isProduction =
	process.argv[process.argv.indexOf('--mode') + 1] === 'production'

const CSSLoader = {
	loader: 'css-loader',
	options: {
		modules: 'global',
		importLoaders: 2,
		camelCase: true,
		sourceMap: false, // turned off as causes delay
	},
}

function webpackConfig() {
	return {
		mode: isProduction ? 'production' : 'development',
		cache: isProduction ? false : true,
		watch: isProduction ? false : true,
		devtool: isProduction ? false : 'eval',
		entry: {
			app: ['./assets/js/app.jsx', './assets/css/app.scss'],
			account: ['./assets/js/account/index.js'],
			checkout: ['./assets/js/checkout/index.js'],
		},
		output: {
			path: path.resolve(__dirname, './dist'),
			filename: '[name].js',
			clean: true,
		},
		externals: {
			jQuery: 'jQuery',
			react: 'React',
			'react-dom': 'ReactDOM',
		},
		resolve: {
			extensions: ['.js', '.jsx'],
			// import 'tippy.js/themes/light.css'
			// import 'tippy.js/dist/tippy.css'
			// alias: {
			// 	'tippy.js': path.resolve(__dirname, './node_modules/tippy.js'),
			// },
		},
		plugins: [
			!isProduction &&
				new BrowserSyncPlugin(
					{
						proxy: 'v8.loc',
						host: 'localhost',
						port: 9000,
						https: {
							key: '/Users/andrew/_ssl/localhost-v8.key',
							cert: '/Users/andrew/_ssl/localhost-v8.crt',
						},
						files: [
							'**/*.php',
							'**/*.css',
							{
								match: '**/*.js',
								options: {
									ignored: 'dist/**/*.js', //the js output folder
								},
							},
						],
						reloadDelay: 0,
					},
					{ injectCss: true }
				),
			new MiniCssExtractPlugin({
				filename: '[name].css',
			}),
			new ProgressBarPlugin({
				format: '  build [:bar] ' + ':percent' + ' (:elapsed seconds)',
				clear: false,
			}),
		],
		module: {
			rules: [
				{
					test: /\.svg/,
					type: 'asset/inline',
				},
				{
					test: /\.s[ac]ss$/i,
					exclude: /node_modules/,
					use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
				},
				{
					test: /\.(png|jpe?g|gif)$/i,
					use: [
						{
							loader: 'file-loader',
						},
					],
				},
				{
					test: /\.(js|jsx)/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: [['@babel/preset-env', { targets: 'defaults' }]],
						},
					},
				},
			],
		},
	}
}

const finalConfig = webpackConfig()

export default finalConfig
