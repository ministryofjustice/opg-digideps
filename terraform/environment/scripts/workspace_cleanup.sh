#!/usr/bin/env bash

if [ $# -eq 0 ]
  then
    echo "Please provide workspaces to be removed."
fi

in_use_workspaces="$@"
reserved_workspaces="default production preproduction integration training production02"

protected_workspaces="$in_use_workspaces $reserved_workspaces"
all_workspaces=$(terraform workspace list|sed 's/*//g')

unset TF_WORKSPACE
export TF_VAR_OPG_DOCKER_TAG=""
export TF_EXIT_CODE="0"

for workspace in $all_workspaces
do
  case "$protected_workspaces" in
    *$workspace*)
      echo "protected workspace: $workspace"
      ;;
    *)
      if [[ $workspace == *"prod"* ]]
      then
        echo "check on this workspace: $workspace as it has reserved word fragment in the title..."
      else
        echo "cleaning up workspace $workspace..."
        terraform workspace select $workspace
        terraform destroy -auto-approve
        if [ $? != 0 ]; then
          export TF_EXIT_CODE="1"
        else
          terraform import "module.eu_west_1[0].aws_cloudwatch_log_group.container_insights" "/aws/ecs/containerinsights/${workspace}/performance"
          # Second destroy to remove performance log group as first destroy recreates it
          terraform destroy -auto-approve
          terraform workspace select default
          terraform workspace delete $workspace
        fi
      fi
      ;;
  esac
done

if [[ $TF_EXIT_CODE == "1" ]]; then
  exit 1
fi
