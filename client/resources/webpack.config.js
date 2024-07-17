const fs = require('fs')
const path = require('path')
const del = require('del')
const CopyPlugin = require('copy-webpack-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

const tag = (new Date()).getTime()

const outputDirWithTimestamp = path.resolve(__dirname, 'public/assets/' + tag)
const baseOutputDir = path.resolve(__dirname, 'public/assets')

module.exports = {
  entry: {
    application: './assets/scss/application.scss',
    common: './assets/javascripts/common.js',
    clientBenefitsCheckForm: './assets/javascripts/pages/clientBenefitsCheckForm.js',
    'formatted-report': './assets/scss/formatted-report.scss',
    fonts: './assets/scss/fonts.scss'
  },
  mode: 'production',
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.scss$/i,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader',
            options: {
              url: {
                filter: (url) => {
                  return url.includes('?inline')
                }
              }
            }
          },
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                includePaths: [
                  'node_modules/govuk_frontend_toolkit/stylesheets',
                  'node_modules/govuk-frontend/govuk/assets',
                  'node_modules/govuk-elements-sass/public/sass'
                ]
              }
            }
          }
        ]
      },
      {
        test: /\.woff2?/i,
        use: 'url-loader'
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader'
      }
    ]
  },
  output: {
    filename: 'javascripts/[name].js',
    path: outputDirWithTimestamp
  },
  plugins: [
    {
      apply: function (compiler) {
        // Delete contents of 'public/assets' before building new files
        compiler.hooks.compilation.tap('CleanPlugin', () => {
          del(['public/assets/*'])
        })
      }
    },
    new CopyPlugin({
      patterns: [
        { from: 'node_modules/jquery/dist/jquery.min.js', to: 'javascripts' },
        { from: 'node_modules/govuk-frontend/govuk/assets/fonts', to: 'stylesheets/fonts' },
        { from: 'node_modules/govuk-frontend/govuk/assets/images', to: path.resolve(__dirname, 'public/images') },
        {
          from: 'node_modules/@ministryofjustice/frontend/moj/assets/images',
          to: path.resolve(__dirname, 'public/images')
        },
        { from: 'node_modules/govuk_frontend_toolkit/images', to: path.resolve(__dirname, 'public/images') },
        { from: 'assets/images', to: path.resolve(__dirname, 'public/images') },
        { from: 'assets/images/generic-images', to: path.resolve(__dirname, 'public') },
        { from: 'assets/images/generic-images', to: path.resolve(__dirname, 'public/images') }
      ]
    }),
    new MiniCssExtractPlugin({
      filename: 'stylesheets/[name].css'
    }),
    {
      apply: function (compiler) {
        compiler.hooks.afterEmit.tapAsync('CopyOutputPlugin', (compilation, callback) => {
          const files = Object.keys(compilation.assets)

          files.forEach(file => {
            // Remove query strings from file names
            const cleanFileName = file.split('?')[0]

            const sourcePath = path.join(outputDirWithTimestamp, cleanFileName)
            const destPath = path.join(baseOutputDir, cleanFileName)

            // Ensure the directory exists
            fs.mkdirSync(path.dirname(destPath), { recursive: true })

            fs.copyFileSync(sourcePath, destPath)
          })

          callback()
        })
      }
    }
  ],
  target: ['web', 'es5']
}
