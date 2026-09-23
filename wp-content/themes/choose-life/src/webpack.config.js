const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const BrowserSyncPlugin = require('browser-sync-v3-webpack-plugin');
const {VueLoaderPlugin} = require('vue-loader');
const CopyPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const installationUrl = 'http://localhost/choose-life-donations/';

const outputPath = '../assets';
const srcPaths = {
	js: './assets/js',
	scss: './assets/scss',
	fonts: './assets/fonts',
	images: './assets/img',
	svg: './assets/svg',
};
const isDevMode = process.env.NODE_ENV !== 'production';
const browserSync = new BrowserSyncPlugin({
		proxy: installationUrl,
		files: [
			outputPath + '/css/*.css',
			'../**/*.php'
		],
		injectCss: true,
	}, {
		reload: true
	}
);
const vueFlags = {
	__VUE_PROD_DEVTOOLS__: JSON.stringify(isDevMode),
};
if (isDevMode) {
	vueFlags.__VUE_OPTIONS_API__ = JSON.stringify(true);
	vueFlags.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = JSON.stringify(true);
}

const config = {
	resolve: {
		extensions: ['.vue', '.js', '.scss'],
		modules: [
			'node_modules'
		]
	},
	entry: {
		admin: {
			import: `${srcPaths.js}/admin.js`
		},
		main: {
			import: `${srcPaths.js}/main.js`
		},
		['my-account']: {
			import: `${srcPaths.js}/my-account.js`
		},
		checkout: {
			import: `${srcPaths.js}/checkout.js`
		}
	},
	output: {
		path: path.resolve(__dirname, outputPath),
		filename: 'js/[name].js',
		// lazy-loaded route chunks get a content hash, so a deploy never mixes
		// a cached old chunk with a new entry file
		chunkFilename: 'js/[name].[contenthash:8].js',
		clean: true,
		assetModuleFilename: 'resources/[hash][ext][query]'
	},
	externalsType: 'window',
	externals: [
		{
			'$': 'jQuery',
			'Vue': 'Vue'
		}
	],
	plugins: [
		new CopyPlugin({
			patterns: [
				{from: srcPaths.fonts, to: `${outputPath}/fonts`},
				{from: srcPaths.images, to: `${outputPath}/img`},
				{from: srcPaths.svg, to: `${outputPath}/svg`},
			],
		}),
		new MiniCssExtractPlugin({
			filename: 'css/[name].css',
			chunkFilename: 'css/[name].[contenthash:8].css',
		}),
		new VueLoaderPlugin(),
		new webpack.DefinePlugin(vueFlags)
	],
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			},
			{
				test: /\.(jpg|jpeg|png|svg)$/i,
				use: 'url-loader?limit=8192',
			},
			{
				test: /\.(woff|woff2|eot|ttf|gif)$/i,
				type: 'asset/resource'
			},
			{
				test: /\.scss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							publicPath: '../',
						}
					},
					{
						loader: 'css-loader',
						options: {
							url: false
						}
					},
					{
						loader: "sass-loader",
						options: {
							implementation: require("sass")
						}
					},
					{
						loader: 'sass-resources-loader',
						options: {
							sourceMap: true,
							resources: [
								'./assets/scss/config/_colors.scss',
								'./assets/scss/config/_paths.scss',
								'./assets/scss/config/_typography.scss',
								'./assets/scss/helpers/mixins/_list.scss',
								'./assets/scss/vendor/bootstrap/scss/_functions.scss',
								'./assets/scss/vendor/bootstrap/scss/_variables.scss',
								'./assets/scss/vendor/bootstrap/scss/_mixins.scss',
							]
						}
					}
				],
			},

		]
	},
	optimization: {
		splitChunks: {
			cacheGroups: {
				vendor: {
					test: /[\\/]node_modules[\\/]/,
					name: 'vendors',
					chunks: 'all'
				}
			}
		}
	}
};

if (isDevMode) {
	config.plugins.push(browserSync);
} else {
	config.optimization.minimize = true;
	config.optimization.minimizer = [
		new TerserPlugin({
			test: /\.js(\?.*)?$/i,
			terserOptions: {
				format: {
					comments: false
				},
			},
			extractComments: false
		})
	];
}

module.exports = config;