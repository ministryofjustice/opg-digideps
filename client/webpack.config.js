var path = require('path');
var del = require('del')
var CopyPlugin = require('copy-webpack-plugin');
var MiniCssExtractPlugin = require('mini-css-extract-plugin');

const tag = (new Date()).getTime();

module.exports = {
    entry: './src/AppBundle/Resources/assets/javascripts/main.js',
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
                            url: false
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
        ]
    },
    output: {
        filename: 'javascripts/application.js',
        path: path.resolve(__dirname, 'web/assets/' + tag)
    },
    optimization: {
        splitChunks: {
            cacheGroups: {
                'formatted-report': {
                    name: 'formatted-report',
                    test: /formatted-report/,
                    priority: -10,
                    chunks: 'all',
                    enforce: true
                }
            }
        }
    },
    plugins: [
        {
            apply: function(compiler) {
                compiler.hooks.compilation.tap('CleanPlugin', () => {
                    del(['web/assets/*']);
                })
            }
        },
        new CopyPlugin([
            { from: 'node_modules/jquery/dist/jquery.min.js', to: 'javascripts' },
            { from: 'node_modules/govuk-frontend/govuk/assets/fonts', to: 'stylesheets/fonts' },
            { from: 'node_modules/govuk-frontend/govuk/assets/images', to: path.resolve(__dirname, 'web/images') },
            { from: 'node_modules/govuk_frontend_toolkit/images', to: path.resolve(__dirname, 'web/images') },
            { from: 'src/AppBundle/Resources/assets/images', to: path.resolve(__dirname, 'web/images') },
        ]),
        new MiniCssExtractPlugin({
            filename: 'stylesheets/application.css',
            chunkFilename: 'stylesheets/[name].css',
        }),
    ]
};
