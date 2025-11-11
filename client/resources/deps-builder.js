// Build CSS and JS dependencies

// TODO remove existing build outputs
// rm -Rf public/assets/*

import * as esbuild from "esbuild"
import path from "path"
import { fileURLToPath } from "url"
import fs from "fs"

const tag = (new Date()).getTime()
const filename = fileURLToPath(import.meta.url)
const dirname = path.dirname(filename)
const outputDirWithTimestamp = path.resolve(dirname, "public/assets/" + tag)

fs.mkdirSync(outputDirWithTimestamp, { recursive: true })

// TODO don't make sourcemaps for prod build
const generateSourceMaps = true

// TODO don't minify code during dev build
const minifyCode = false

// use es2015 as the JS target, for parity with govuk frontend
const bundleJs = async function (entryPoints, outFile) {
  return esbuild.build({
    entryPoints: entryPoints,
    bundle: true,
    minify: minifyCode,
    sourcemap: generateSourceMaps,
    target: ["es2015"],
    outfile: path.resolve(outputDirWithTimestamp, outFile)
  })
}

// JS COMPILATION
Promise
  .all([
    bundleJs(["./assets/javascripts/common.js"], "javascripts/common.js"),
    bundleJs(["./assets/javascripts/pages/clientBenefitsCheckForm.js"], "javascripts/clientBenefitsCheckForm.js"),
    bundleJs(["./node_modules/jquery/dist/jquery.min.js"], "javascripts/jquery.min.js")
  ])
  .then(r => {
    r.forEach(item => {
      if (item.errors.length > 0) {
        console.error("Error encountered while compiling JS", item.errors)
      }
    })
  })
  .finally(() => console.log("Finished compiling JS"))

// COPY IMAGES
const imagesToCopy = [
  { from: "assets/images/generic-images/", to: "public/" },
  { from: "assets/images/generic-images/", to: "public/images/" },
  { from: "node_modules/govuk-frontend/dist/govuk/assets/fonts/", to: "public/stylesheets/fonts/" },
  { from: "node_modules/govuk-frontend/dist/govuk/assets/images/", to: "public/images/" },
  {
    from: "node_modules/@ministryofjustice/frontend/moj/assets/images/",
    to: "public/images/"
  },
  { from: "node_modules/govuk_frontend_toolkit/images/", to: "public/images/" },
  { from: "assets/images/", to: "public/images/" }
]

imagesToCopy.forEach(copySpec => {
  const destPath = path.resolve(dirname, copySpec.to)
  fs.mkdirSync(path.dirname(destPath), { recursive: true })
  fs.cpSync(copySpec.from, destPath, { recursive: true })
})

console.log("Finished copying image files")

// COMPILE CSS
