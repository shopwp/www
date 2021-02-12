import webpack from 'webpack';
import path from 'path';
import ProgressBarPlugin from 'progress-bar-webpack-plugin';

module.exports = {
  mode: 'development',
  watch: true,
  entry: {
    app: path.resolve(__dirname, 'assets/js/app/app.js'),
    account: path.resolve(__dirname, 'assets/js/app/account/index.js'),
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'assets/prod'),
    chunkFilename: '[name].min.js',
  },
  resolve: {
    extensions: ['*', '.js', '.jsx'],
  },
  plugins: [
    new webpack.ProvidePlugin({
      React: 'react',
    }),
    new webpack.optimize.ModuleConcatenationPlugin(),
    new ProgressBarPlugin(),
  ],
  optimization: {
    moduleIds: 'named',
    chunkIds: 'named',
  },
  module: {
    rules: [
      {
        test: /\.svg/,
        type: 'asset/inline',
      },
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.s[ac]ss$/i,
        use: [
          // Creates `style` nodes from JS strings
          'style-loader',
          // Translates CSS into CommonJS
          'css-loader',
          // Compiles Sass to CSS
          'sass-loader',
        ],
      },
      {
        test: /\.(js|jsx)$/i,
        exclude: /node_modules/,
        enforce: 'pre',
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env', '@babel/preset-react'],
            },
          },
        ],
      },
    ],
  },
};
