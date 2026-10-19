#!/usr/bin/env bash

if [ $# -eq 0 ]
then
  echo "Please provide workspaces to be removed."
fi

terraform_destroy() {
  local workspace="$1"
  local attempt=1

  while [[ $attempt -le 3 ]]
  do
    if terraform destroy -auto-approve; then
      return 0
    fi

    if [[ $attempt -lt 3 ]]; then
      echo "Terraform destroy attempt $attempt failed for $workspace. Retrying..."
      sleep 10
    fi

    attempt=$((attempt + 1))
  done

  echo "Terraform destroy failed after 3 attempts for $workspace."
  return 1
}

in_use_workspaces="$@"
reserved_workspaces="default development staging integration training preproduction preproduction2 production production02"

protected_workspaces="$in_use_workspaces $reserved_workspaces"
all_workspaces=$(terraform workspace list | sed 's/*//g')

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

        if ! terraform workspace select "$workspace"; then
          echo "Unable to select workspace $workspace."
          export TF_EXIT_CODE="1"
          continue
        fi

        if ! terraform_destroy "$workspace"; then
          export TF_EXIT_CODE="1"
          terraform workspace select default
          continue
        fi

        if terraform import "module.eu_west_1[0].aws_cloudwatch_log_group.container_insights" "/aws/ecs/containerinsights/${workspace}/performance"; then
          if ! terraform_destroy "$workspace"; then
            export TF_EXIT_CODE="1"
            terraform workspace select default
            continue
          fi
        else
          echo "Log group not found, skipping second destroy"
        fi

        terraform workspace select default

        if ! terraform workspace delete "$workspace"; then
          export TF_EXIT_CODE="1"
        fi
      fi
      ;;
  esac
done

if [[ $TF_EXIT_CODE == "1" ]]; then
  exit 1
fi
