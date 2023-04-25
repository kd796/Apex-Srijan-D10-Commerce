let mix = require('laravel-mix')
let glob = require('import-glob-loader')
let fs = require('fs')
let ini = require('ini')

let CopyWebpackPlugin = require('copy-webpack-plugin')
let ImageminPlugin = require('imagemin-webpack-plugin').default

//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Config
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

const config = {
    srcPath: 'resources/src',
    publicPath: 'resources/dist'
}


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Mix Options
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.setPublicPath(config.publicPath)
mix.disableSuccessNotifications()


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Browser Options
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.options({
    processCssUrls: false,
    postCss: [
        require('autoprefixer')({
            grid: true,
            browsers: ['last 2 versions', 'ie >= 11']
        }),
        require('postcss-object-fit-images'),
    ]
})


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// JS
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

// mix.js(config.srcPath + '/js/scripts.js', '/js')
//     .extract(['vue'])
//     .sourceMaps()


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// CSS
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.sass(config.srcPath + '/scss/styles.scss', config.publicPath + '/css')
    .sourceMaps()


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Versioning
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.version()


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Config / Plugins
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.scss/,
                loader: 'import-glob-loader'
            },
        ]
    },
    plugins: []
})
