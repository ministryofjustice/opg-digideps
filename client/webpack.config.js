var path = require('path');
var MiniCssExtractPlugin = require('mini-css-extract-plugin');

function recursiveIssuer(m) {
    console.log(m.issuer);
    if (m.issuer) {
        return recursiveIssuer(m.issuer);
    } else if (m.name) {
        return m.name;
    } else {
        return false;
    }
}

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
                    'css-loader',
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
        path: path.resolve(__dirname, 'web/assets/gt')
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
        new MiniCssExtractPlugin({
            filename: 'stylesheets/application.css',
            chunkFilename: 'stylesheets/[name].css',
        }),
    ]
};
