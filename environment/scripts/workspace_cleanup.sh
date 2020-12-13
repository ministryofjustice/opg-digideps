#!/usr/bin/env sh

if [ $# -eq 0 ]
  then
    echo "Please provide workspaces to be removed."
fi

function getWorkspaces {
  terraform workspace list
}

in_use_workspaces="$@"
reserved_workspaces="default production preproduction development develop integration training production02"

protected_workspaces="$in_use_workspaces $reserved_workspaces"
all_workspaces=$(terraform workspace list|sed 's/*//g')

unset TF_WORKSPACE
export TF_VAR_OPG_DOCKER_TAG=""

for workspace in $all_workspaces
do
  case "$protected_workspaces" in
    *$workspace*)
      echo "protected workspace: $workspace"
      ;;
    *)
      if [[ $workspace == *"prod"* ]]
      then
        echo "check on this workspace: $workspace as it has prod in the title..."
      else
        echo "cleaning up workspace $workspace..."
#        terraform workspace select $workspace
#        terraform destroy -auto-approve
#        if [ $? != 0 ]; then
#          local TF_EXIT_CODE = 1
#        fi
#        terraform workspace select default
#        terraform workspace delete $workspace
      fi
      ;;
  esac
done

if [TF_EXIT_CODE == 1]; then
  exit 1
fi
