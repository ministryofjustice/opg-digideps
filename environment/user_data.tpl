#cloud-boothook
#!/bin/bash -ex

#let's log the output
exec > >(tee /var/log/user-data.log|logger -t user-data -s 2>/dev/console) 2>&1

function download()
{
    #try downloading a file for 5 mins
    local module_path=$${1}
    local retry_count_down=30
    local base_url="https://raw.githubusercontent.com/ministryofjustice/opg-bootstrap/spectrum-deployment-branch"
    while ! wget --no-verbose --retry-connrefused --random-wait -O $${module_path} "$${base_url}/$${module_path}" && [ $${retry_count_down} -gt 0 ] ; do
        retry_count_down=$((retry_count_down - 1))
        sleep 10
    done
}

function module()
{
    local module_path=$${1}
    if [ ! -e $${module_path} ]; then
        echo $${module_path}: Downloading
        mkdir -p modules
        download $${module_path}
    fi
    echo $${bmodule_path}: Loading
    source $${module_path}
}

readonly IS_SALTMASTER=${IS_SALTMASTER}
readonly HAS_DATA_STORAGE="${HAS_DATA_STORAGE}"

readonly OPG_ROLE=${OPG_ROLE}
readonly OPG_STACKNAME=${OPG_STACKNAME}
readonly OPG_PROJECT=${OPG_PROJECT}
readonly OPG_ACCOUNT_ID=${OPG_ACCOUNT_ID}

readonly OPG_ENVIRONMENT=${OPG_ENVIRONMENT}
readonly OPG_SHARED_SUFFIX=${OPG_SHARED_SUFFIX}
readonly OPG_DOMAIN=${OPG_DOMAIN}
readonly OPG_VPCNAME=${OPG_VPCNAME}

readonly ETH0_MTU=9001

module modules/00-start.sh
module modules/10-volumes.sh
module modules/20-docker.sh
module modules/90-salt.sh



module modules/99-end.sh
