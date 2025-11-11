// Build CSS and JS dependencies

// Remove existing build outputs
// rm -Rf public/assets/*

import * as esbuild from "esbuild"
import path from "path"
import { fileURLToPath } from "url"

const tag = (new Date()).getTime()
const filename = fileURLToPath(import.meta.url)
const outputDirWithTimestamp = path.resolve(path.dirname(filename), 'public/assets/' + tag)

// use es2015 as the JS target, for parity with govuk frontend
console.log(await esbuild.build({
  entryPoints: ["./assets/javascripts/common.js"],
  bundle: true,
  minify: true,
  sourcemap: true,
  target: ["es2015"],
  outfile: path.resolve(outputDirWithTimestamp, "javascripts/common.js")
}))
