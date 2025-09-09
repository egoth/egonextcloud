// webpack.config.js
const path = require('path')
const webpack = require('webpack')

module.exports = {
  mode: 'production',
  entry: './src/main.js',
  output: {
    path: path.resolve(__dirname, 'js'),
    filename: 'main.js',
  },
  target: 'web',
  resolve: {
    fallback: {
      path: require.resolve('path-browserify'),
      string_decoder: require.resolve('string_decoder/'),
      buffer: require.resolve('buffer/'),
      // Se in futuro compaiono altri moduli Node:
      // stream: require.resolve('stream-browserify'),
      // crypto: require.resolve('crypto-browserify'),
    },
  },
  plugins: [
    // Rende disponibile Buffer globalmente (alcuni pacchetti se lo aspettano)
    new webpack.ProvidePlugin({
      Buffer: ['buffer', 'Buffer'],
    }),
  ],
}
