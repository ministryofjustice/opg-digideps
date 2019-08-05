# opg-digideps
Digideps: Managed by opg-org-infra &amp; Terraform

# Digi-deps deploy
Terraform for building and deploying Digi-deps environments

# Prerequisites
    - docker
    - make
    - terraform-docs
    - jq
    
    optionally;
    - aws-vault for credentials handling
    - direnv (to set shell exports, see .envrc)
    
# Usage
```bash
# ensure your environment is setup:
export TF_WORKSPACE=myawesomeenvironment
export TF_VAR_OPG_DOCKER_TAG=1.0.myawesometag
export AWS_ACCESS_KEY_ID=AKIAEXAMPLE 
export AWS_SECRET_ACCESS_KEY=cbeamsglittering
cd environment
make

# alternatively, using aws-vault:
export TF_WORKSPACE=myawesomeenvironment
export TF_VAR_OPG_DOCKER_TAG=1.0.myawesometag
cd environment
aws-vault exec identity make
```

Check .envrc if you need to adjust your terraform backend
