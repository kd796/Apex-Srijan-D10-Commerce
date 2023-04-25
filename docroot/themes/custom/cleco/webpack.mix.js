let mix = require("laravel-mix")
let glob = require("import-glob-loader")
let fs = require("fs")
let ini = require("ini")

let webpack = require("webpack");
let CopyWebpackPlugin = require("copy-webpack-plugin")
let ImageminPlugin = require("imagemin-webpack-plugin").default


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Config
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

const env = ini.parse(fs.readFileSync("./.env", "utf-8"))
const config = {
  srcPath: "./src",
  publicPath: "./dist",
  url: env.ENVIRONMENT_URL,
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
    require("autoprefixer")({
      grid: true,
      browsers: ["last 2 versions", "ie >= 11"]
    }),
    require("postcss-object-fit-images"),
  ]
})


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// JS
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix
    .js(config.srcPath + "/js/drupal.js", "/js")
    .js(config.srcPath + "/js/scripts.js", "/js")
    .extract([
        "@babel/polyfill",
        "@tweenjs/tween.js",
        "can-deparam",
        "can-param",
        "axios",
        "moment",
        "three",
        "urijs",
        "vue",
        "vue-scrollto",
        "vue-table-component",
        "vue-tabs-component",
        "vue2-google-maps",
        "vue2-siema",
    ])
    .sourceMaps()


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// CSS
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.sass(config.srcPath + "/scss/styles.scss", config.publicPath + "/css")
    .sass(config.srcPath + "/scss/cp.scss", config.publicPath + "/css")
    .sass(config.srcPath + "/scss/editor.scss", config.publicPath + "/css")
    .sourceMaps()


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Fonts
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
mix
    .copyDirectory(config.srcPath + "/fonts", config.publicPath + "/fonts")


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Versioning
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.version()
mix.version([
  config.publicPath + "/img/favicon.png"
])


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Local Dev
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.browserSync({
  open: false,
  proxy: config.url,
  https: config.url.startsWith('https'),
  notify: false,
  files: [
    config.publicPath + "/js/**/*",
    config.publicPath + "/css/**/*",
    {
      match: [
        "**/*.html",
        "**/*.php",
        "**/*.twig",
        "**/*.theme",
        "**/*.module",
      ]
    }
  ]
})


//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
// Config / Plugins
//–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––

mix.webpackConfig({
  module: {
    rules: [
      {
        test: /\.scss/,
        loader: "import-glob-loader"
      },
    ]
  },
  plugins: [
    new CopyWebpackPlugin([
      {
        from: config.srcPath + "/img",
        to: "img",
      }
    ]),
    new ImageminPlugin({
      test: "img/*",
      svgo: {
        plugins: [
          {removeViewBox: false},
        ]
      }
    }),
    new ImageminPlugin({
      test: "img/icons/*.svg",
      svgo: {
        plugins: [
          {removeTitle: true},
          {removeUselessStrokeAndFill: true},
          {removeViewBox: false},
          {collapseGroups: true},
          {convertColors: {currentColor: true}},
          {cleanupIDs: {minify: false}},
        ]
      }
    }),
      new webpack.DefinePlugin({
        'process.env': env
      }),
  ]
})
