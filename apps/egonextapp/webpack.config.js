// webpack.config.js
const path = require('path')

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
      // Polyfill per moduli Node usati da @nextcloud/files
      path: require.resolve('path-browserify'),
      string_decoder: require.resolve('string_decoder/'),
      // Se in futuro compaiono altri simili errori (“Module not found: stream/buffer/crypto…”)
      // aggiungi i corrispondenti fallback qui
      // es.: stream: require.resolve('stream-browserify'),
      //      buffer: require.resolve('buffer/'),
    },
  },
  module: {
    rules: [
      // Se usi Babel:
      // {
      //   test: /\.m?js$/,
      //   exclude: /node_modules/,
      //   use: {
      //     loader: 'babel-loader',
      //     options: { presets: ['@babel/preset-env'] }
      //   }
      // }
    ],
  },
}
