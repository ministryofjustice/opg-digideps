#!/usr/bin/env bash
# Usage: Get the semver range from the content of a
# versions.tf file

# Presume the imcing stream is a `versions.tf` file and
# parse out the required_version value
sed -r -n 's/.*required_version.*"(.*)"$/\1/p' <&0
