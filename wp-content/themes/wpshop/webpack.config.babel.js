const webpack = require('webpack');
const utils = require('webpack-config-utils');
const WriteFilePlugin = require('write-file-webpack-plugin');
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const validate = require('webpack-validator');
const path = require('path');
const THEME_NAME = 'wpshop';
const HOST = 'localhost';
const PORT = 3000;
const env = process.env.NODE_ENV;
const webpackUtils = utils.getIfUtils(env);
const ifProd = webpackUtils.ifProd;
const ifNotProd = webpackUtils.ifNotProd;
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const HappyPack = require('happypack');


module.exports = () => {

  return {

    /*

    false === smaller bundle size
    eval === faster bundle time

    */
    devtool: ifProd('source-map', 'eval'),
    entry: {
      vendor: ["validator", "dateFormat"],
      app: [
        path.resolve('assets/js/app/app.js'),
      ],
      docs: path.resolve('assets/js/app/docs/docs')
    },
    output: {
      path: path.resolve('assets/prod/js'),
      publicPath: `//${HOST}:${PORT}/wp-content/themes/${THEME_NAME}/`,
      filename: '[name].min.js'
    },
    plugins: [
      new webpack.ProvidePlugin({
        // $: "jquery/src/jquery",
        // jQuery: "jquery/src/jquery",
        // "window.jQuery": "jquery",
        validator: "validator",
        ScrollMagic: "ScrollMagic",
        crypto: "crypto",
        Pace: "pace-progress"
      }),
      new webpack.DefinePlugin({
        'process.env.NODE_ENV': JSON.stringify('development')
      }),
      // new webpack.optimize.OccurrenceOrderPlugin(),
      // new webpack.NoEmitOnErrorsPlugin(),
      new ProgressBarPlugin(),
      new WriteFilePlugin(),
      // new webpack.optimize.CommonsChunkPlugin({
      //   name: "vendor"
      // }),
      new CopyWebpackPlugin([{
        from: path.resolve('assets/imgs'),
        to: path.resolve('assets/prod/imgs')
      }], {
        ignore: [
          ifProd('', '**/*')
        ]
      }),
      new ImageminPlugin({
        disable: ifNotProd(),
        test: /\.(jpe?g|png|gif|svg)$/i
      }),
      new ExtractTextPlugin({
        filename: '../css/[name].min.css'
      }),
      new OptimizeCssAssetsPlugin({
        cssProcessor: require('cssnano'),
        cssProcessorOptions: {
          discardComments: {
            removeAll: false
          }
        },
        canPrint: true
      }),
      new webpack.optimize.UglifyJsPlugin({
        comments: false,
        compress: {
          screw_ie8: true,
          unused: true,
          dead_code: true,
          drop_debugger: true,
          conditionals: true,
          evaluate: true,
          sequences: true,
          booleans: true,
          properties: true,
          loops: true
        }
      }),
      new HappyPack({
        loaders: ['babel-loader'],
      })
    ],
    resolve: {
      extensions: ['.js'],
      alias: {
        // jquery: "jquery/src/jquery",
        // $: "jquery/src/jquery",
        validator: "validator/index",
        // ScrollMagic: "scrollmagic/scrollmagic/uncompressed/ScrollMagic",
        crypto: "crypto",
        Pace: "pace-progress/pace"
      }
    },
    module: {
      rules: [{
        test: /\.js$/,
        exclude: /node_modules/,
        enforce: 'pre',
        use: [
          {
            loader: 'happypack/loader',
            options: {
              plugins: ['lodash'],
              presets: [
                [ 'es2015', { modules: false, loose: true } ]
              ]
            }
          }
        ]
      },
      {
        test: /\.(png|jpe?g|gif|woff|woff2|eot|ttf|svg)$/,
        use: [{
          loader: 'url-loader?limit=100000'
        }]
      },
      {
        test: /\.scss$/,
        use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: ['css-loader?url=false', 'postcss-loader', 'sass-loader', 'resolve-url-loader']
        })
      }, {
        test: require.resolve("pace-progress"),
        loader: "imports-loader?define=>false"
      }]
    }
  }

}
