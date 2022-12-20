#!/usr/bin/env bash

WORKSPACE=${WORKSPACE:-$CIRCLE_BRANCH}
WORKSPACE=${WORKSPACE//[^[:alnum:]]/}
WORKSPACE=${WORKSPACE,,}
WORKSPACE=${WORKSPACE:0:14}
echo "export TF_WORKSPACE=${WORKSPACE}"

VERSION=${VERSION:-$(cat ~/project/VERSION 2>/dev/null)}
if [ "${INIT_APPLY}" == "true" ]
then
  export TF_WORKSPACE=training
  export LIVE_DOCKER_TAG=$(terraform output | grep opg_docker_tag | awk -F'=' '{print $2}' | sed 's/ //g')
  echo "export TF_VAR_OPG_DOCKER_TAG=${LIVE_DOCKER_TAG}"
elif [ "${REAPPLY}" == "true" ]
then
  export TF_WORKSPACE=${WORKSPACE}
  export CURRENT_DOCKER_TAG=$(terraform output | grep opg_docker_tag | awk -F'=' '{print $2}' | sed 's/ //g')
  echo "export TF_VAR_OPG_DOCKER_TAG=${CURRENT_DOCKER_TAG}"
else
  echo "export TF_VAR_OPG_DOCKER_TAG=${VERSION}"
fi

echo "export VERSION=${VERSION}"
