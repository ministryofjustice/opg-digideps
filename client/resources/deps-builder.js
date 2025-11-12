// Build CSS and JS dependencies
import * as esbuild from "esbuild"
import path from "path"
import { fileURLToPath } from "url"
import fs from "fs"
import * as sass from "sass-embedded"
import fsPromises from "node:fs/promises"

const tag = (new Date()).getTime()
const filename = fileURLToPath(import.meta.url)
const dirname = path.dirname(filename)

// this directory is just used to timestamp the assets and won't contain anything
const outputDirWithTimestamp = path.resolve(dirname, "public/assets/" + tag)

// assets will actually be loaded from this directory
const outputDir = path.resolve(dirname, "public/assets/fallback")

// remove existing build outputs
fs.globSync("./public/*").forEach(generatedDir => {
  fs.rmSync(generatedDir, { recursive: true, force: true })
})

// set up output directories
fs.mkdirSync(outputDirWithTimestamp, { recursive: true })
fs.mkdirSync(outputDir, { recursive: true })

const cssOutputDir = path.resolve(outputDir, "stylesheets")
fs.mkdirSync(cssOutputDir, { recursive: true })

const fontDir = path.resolve(cssOutputDir, "fonts")
fs.mkdirSync(fontDir, { recursive: true })

const javascriptsDir = path.resolve(outputDir, "javascripts")
fs.mkdirSync(javascriptsDir, { recursive: true })

// TODO don't make sourcemaps for prod build
const generateSourceMaps = true

// TODO don't minify code during dev build
const minifyCode = false

// use es2015 as the JS target, for parity with govuk frontend
const bundleJS = async function (entryPoints, outFile) {
  return esbuild.build({
    entryPoints: entryPoints,
    bundle: true,
    minify: minifyCode,
    sourcemap: generateSourceMaps,
    target: ["es2015"],
    outfile: path.resolve(javascriptsDir, outFile)
  })
}

// JS COMPILATION
Promise
  .all([
    bundleJS(["./assets/javascripts/common.js"], "common.js"),
    bundleJS(["./assets/javascripts/pages/clientBenefitsCheckForm.js"], "clientBenefitsCheckForm.js"),
    bundleJS(["./node_modules/jquery/dist/jquery.min.js"], "jquery.min.js")
  ])
  .then(r => {
    r.forEach(item => {
      let hasErrors = false

      if (item.errors.length > 0) {
        hasErrors = true
        console.error("Error encountered while compiling JS: ", item.errors)
      }

      console.log("Finished compiling JS")

      if (hasErrors) {
        console.error("Error occurred while compiling JS")

        process.exit(1)
      }
    })
  })

// COPY IMAGES
const imagesToCopy = [
  { from: "node_modules/govuk-frontend/dist/govuk/assets/fonts/", to: fontDir },
  { from: "assets/images/generic-images/", to: "public/images/" },
  { from: "assets/images/", to: "public/images/" },
  { from: "node_modules/govuk-frontend/dist/govuk/assets/images/", to: "public/images/" },
  {
    from: "node_modules/@ministryofjustice/frontend/moj/assets/images/",
    to: "public/images/"
  },
  { from: "node_modules/govuk_frontend_toolkit/images/", to: "public/images/" }
]

imagesToCopy.forEach(copySpec => {
  const destPath = path.resolve(dirname, copySpec.to)
  fs.mkdirSync(path.dirname(destPath), { recursive: true })
  fs.cpSync(copySpec.from, destPath, { recursive: true })
})

console.log("Finished copying image files")

// COMPILE CSS
const options = {
  loadPaths: [
    dirname,
    "node_modules/govuk_frontend_toolkit/stylesheets",
    "node_modules/govuk-frontend/dist/govuk/assets",
    "node_modules/govuk-elements-sass/public/sass",
    "assets/scss"
  ]
}

const bundleCSS = async function (entryPath, outFile) {
  return sass.compileAsync(entryPath, options)
    .then(cssResult => {
      return esbuild.transform(
        cssResult.css,
        {
          loader: "css",
          minify: minifyCode,
        }
      )
    })
    .then(async function (cssResult) {
      return await fsPromises.writeFile(path.resolve(cssOutputDir, outFile), cssResult.code)
    })
}

Promise
  .all([
    bundleCSS("./assets/scss/application.scss", "application.css"),
    bundleCSS("./assets/scss/fonts.scss", "fonts.css"),
    bundleCSS("./assets/scss/formatted-report.scss", "formatted-report.css")
  ])
  .then(r => {
    let hasErrors = false

    r.forEach(item => {
      if (item !== undefined) {
        hasErrors = true
        console.error("Error encountered while compiling CSS: ", item)
      }
    })

    console.log("Finished compiling CSS")

    if (hasErrors) {
      console.error("Error occurred while compiling CSS")
      process.exit(1)
    }
  })
