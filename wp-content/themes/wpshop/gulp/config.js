////////////
// Config //
////////////

import argvs from 'yargs'
import webpack from 'webpack'
import UglifyJsPlugin from 'uglifyjs-webpack-plugin'
import browserSync from 'browser-sync'
import willChange from 'postcss-will-change'
import willChangeTransition from 'postcss-will-change-transition'
import mqpacker from 'css-mqpacker'
import colormin from 'postcss-colormin'
import cssstats from 'postcss-cssstats'
import cssnano from 'cssnano'
import autoprefixer from 'autoprefixer'
import presetEnv from 'postcss-preset-env'
import Visualizer from 'webpack-visualizer-plugin'
import ParallelUglifyPlugin from 'webpack-parallel-uglify-plugin'
import ProgressBarPlugin from 'progress-bar-webpack-plugin'
import path from 'path'
import MiniCssExtractPlugin from 'mini-css-extract-plugin'
import OptimizeCSSAssetsPlugin from 'optimize-css-assets-webpack-plugin'
import OptimizeCSSClassnamesPlugin from 'optimize-css-classnames-plugin'

/*

Main Config Object

*/
var config = {
  files: {
    js: './assets/js/**/*.js',
    css: './assets/css/**/*.scss',
    jsEntry: './assets/js/app/app.js',
    cssEntry: './assets/css/app/app.scss'
  },
  folders: {
    plugin: './',
    dist: './assets/prod',
    cache: './node_modules/.cache'
  },
  names: {
    css: 'app.min.css',
    js: 'app.min.js'
  },
  bs: browserSync.create(),
  serverName: 'wpshop.test',
  isBuilding: false
}

/*

Webpack Config

*/
function webpackConfig(outputFinalname) {
  var webpackConfigObj = {
    watch: false,
    mode: config.isBuilding ? 'production' : 'development',
    cache: true,

    // IMPORTANT: This entry will override an entry set within webpack stream
    entry: {
      app: config.files.jsEntry
    },
    output: {
      filename: '[name].min.js',
      path: __dirname + '/assets/prod',
      chunkFilename: '[name].min.js'
    },
    resolve: {
      extensions: ['.js']
    },
    plugins: [new webpack.optimize.ModuleConcatenationPlugin(), new ProgressBarPlugin()],
    optimization: {
      splitChunks: {
        name: true,
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/](react|react-dom)[\\/]/,
            name: 'vendor',
            chunks: 'all'
          }
        }
      },
      minimizer: [
        new UglifyJsPlugin({
          parallel: true,
          cache: true,
          parallel: true,
          extractComments: config.isBuilding ? true : false,
          uglifyOptions: {
            compress: config.isBuilding ? true : false,
            ecma: 6,
            mangle: config.isBuilding ? true : false,
            safari10: true
          },
          sourceMap: config.isBuilding ? false : true
        }),
        new OptimizeCSSAssetsPlugin({})
      ]
    },
    module: {
      rules: [
        {
          test: /\.css$/,
          use: [MiniCssExtractPlugin.loader, 'css-loader']
        },
        {
          test: /\.(js|jsx)$/i,
          exclude: /node_modules/,
          enforce: 'pre',
          use: [
            {
              loader: 'babel-loader',
              options: {
                babelrcRoots: ['.', './_tmp/*'],
                presets: ['@babel/preset-env', '@babel/preset-react']
              }
            }
          ]
        }
      ]
    }
  }

  if (config.isBuilding) {
    webpackConfigObj.plugins.push(
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': JSON.stringify('production')
      })
    )
  }

  return webpackConfigObj
}

/*

Postcss Config

*/
function postCSSPlugins() {
  var plugins = [
    willChangeTransition,
    willChange,
    autoprefixer(),
    presetEnv(), // Allows usage of future CSS
    colormin({
      legacy: true
    })
  ]

  // Only run if npm run gulp --build
  if (config.isBuilding) {
    plugins.push(cssnano({ zindex: false }))
  }

  return plugins
}

/*

Style Lint Config

*/
function stylelintConfig() {
  return {
    config: {
      rules: {
        'declaration-block-no-duplicate-properties': true,
        'block-no-empty': true,
        'no-extra-semicolons': true,
        'font-family-no-duplicate-names': true
      }
    },
    debug: true,
    reporters: [{ formatter: 'string', console: true }]
  }
}

config.postCSSPlugins = postCSSPlugins
config.webpackConfig = webpackConfig
config.stylelintConfig = stylelintConfig

export default config
