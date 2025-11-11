// Build CSS and JS dependencies

// TODO remove existing build outputs
// rm -Rf public/assets/*

import * as esbuild from "esbuild"
import path from "path"
import { fileURLToPath } from "url"

const tag = (new Date()).getTime()
const filename = fileURLToPath(import.meta.url)
const outputDirWithTimestamp = path.resolve(path.dirname(filename), "public/assets/" + tag)

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

Promise
  .all([
    bundleJs(["./assets/javascripts/common.js"], "javascripts/common.js"),
    bundleJs(["./assets/javascripts/pages/clientBenefitsCheckForm.js"], "javascripts/clientBenefitsCheckForm.js")
  ])
  .then(r => {
    r.forEach(item => {
      if (item.errors.length > 0) {
        console.error("Error encountered while compiling JS", item.errors)
      }
    })
  })
  .finally(() => console.log("Finished compiling JS"))
