#!/usr/bin/env bash
# Bash script to change terraform versions via tfswitch for local dev.
# Ensures that tfswitch, jq, versions.tf are present before running
#

set -e
# current directory
current_dir=$(pwd)
# directory of this script
script_dir=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
# versions.tf file path
file="${current_dir}/versions.tf"
# path to tfswitch (from which call)
tfswitch_path=$(which tfswitch || exit 0)
# path to jq
jq_path=$(which jq || exit 0)
# verbose output defaults to 0
verbose=1

# export these values as they are likely useful
# to parent scope
export TERRAFORM_VERSION_RANGE=""
export TERRAFORM_INSTALLED_VERSION=""


# fetch option data
while getopts :v flag
do
    case "${flag}" in
        v) verbose=1;;
    esac
done

# If ${file} is not a file, then fail
if [ ! -f "${file}" ]; then
    echo -e "error: Terraform versions file not found: [${file}]" >&2
    exit 1
fi

# If tfswitch path is not a file, then fail
if [ ! -f "${tfswitch_path}" ]; then
	echo -e "error: tfswitch not found: [${tfswitch_path}]. Run: brew install warrensbox/tap/tfswitch" >&2
    exit 1
fi

# Check jq installed
if [ ! -f "${jq_path}" ]; then
	echo -e "error: jq not found: [${jq_path}]. Run: brew install jq" >&2
    exit 1
fi

# Now fetch the terraform version using sibling script
TERRAFORM_VERSION_RANGE=$(cat ${file} | ${script_dir}/./terraform-version.sh)
# Now run tfswitch
echo
echo -e "Running tfswitch.."
tfswitch

# Now capture the version in use - can only do this after tfswitch has run
# - strip quotes from the json output
TERRAFORM_INSTALLED_VERSION=$(terraform version -json | jq '.terraform_version' | sed -e 's/"//g')

# If verbose is turned on, output the info from this run
if [ "${verbose}" == "1" ]; then
    echo
    echo -e "--- Info ---"
    echo -e "- Called from directory: [${current_dir}]"
    echo -e "- Script location: [${script_dir}]"
    echo -e "- Terraform versions file found: [${file}]"
    echo -e "- tfswitch command path: [${tfswitch_path}]"
    echo -e "- jq command path: [${jq_path}]"
    echo -e "- Terraform version range found: [${TERRAFORM_VERSION_RANGE}]"
    echo -e "- Terraform installed version: [${TERRAFORM_INSTALLED_VERSION}]"
    echo -e "---"
fi
