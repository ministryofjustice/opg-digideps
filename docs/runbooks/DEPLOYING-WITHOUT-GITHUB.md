# Building, Scanning, Pushing and Deploying Without GitHub Actions

This document describes how to perform the equivalent of the GitHub Actions Docker build and deployment workflow manually if GitHub or GitHub Actions is unavailable.

The process consists of:

1. Building Docker images locally
2. Running Snyk scans locally
3. Pushing images to ECR
4. Applying Terraform changes
5. Validating in a non-production environment

> **Important:** Always test the entire process in a non-production environment before deploying to production.

---

# Prerequisites

## Required Software

### Docker

Verify Docker is installed and running:

```bash
docker version
docker buildx version
```

### AWS CLI

Verify AWS CLI is installed:

```bash
aws --version
```

### Terraform

Verify Terraform is installed:

```bash
terraform version
```

### Git

```bash
git --version
```

### Snyk (optional but recommended)

Install and authenticate Snyk:

```bash
snyk auth
```

or set:

```bash
export SNYK_TOKEN=<your-token>
```

---

# Build Docker Images Locally

The GitHub workflow builds the following images:

| Service | Dockerfile | Platform |
|----------|------------|-----------|
| client-webserver | client/docker/web/Dockerfile | arm64 |
| client | client/docker/app/Dockerfile | arm64 |
| client-devtools | client/docker/app/Dockerfile (devtools target) | arm64 |
| api-webserver | api/docker/web/Dockerfile | arm64 |
| api | api/docker/app/Dockerfile | arm64 |
| api-devtools | api/docker/app/Dockerfile (devtools target) | arm64 |
| sync | orchestration/Dockerfile | arm64 |
| htmltopdf | htmltopdf/Dockerfile | arm64 |
| file-scanner | file-scanner/Dockerfile | arm64 |
| dr-backup | disaster-recovery/backup/Dockerfile | arm64 |
| test | playwright/Dockerfile | arm64 |
| custom-sql-lambda | lambdas/functions/custom_sql_query/Dockerfile | amd64 |
| mock-sirius | mock-sirius/Dockerfile | amd64 |

---

# Example Builds

## Client

```bash
docker buildx build \
  -f client/docker/app/Dockerfile \
  --platform linux/arm64 \
  --build-arg PLATFORM=arm64 \
  --target runtime \
  --load \
  -t client:latest \
  .
```

## Client Devtools

```bash
docker buildx build \
  -f client/docker/app/Dockerfile \
  --platform linux/arm64 \
  --build-arg PLATFORM=arm64 \
  --target devtools \
  --load \
  -t client-devtools:latest \
  .
```

## API

```bash
docker buildx build \
  -f api/docker/app/Dockerfile \
  --platform linux/arm64 \
  --build-arg PLATFORM=arm64 \
  --target runtime \
  --load \
  -t api:latest \
  .
```

## API Webserver

```bash
docker buildx build \
  -f api/docker/web/Dockerfile \
  --platform linux/arm64 \
  --build-arg PLATFORM=arm64 \
  --load \
  -t api-webserver:latest \
  .
```

## Sync

```bash
cd orchestration

docker buildx build \
  -f Dockerfile \
  --platform linux/arm64 \
  --load \
  -t sync:latest \
  .

cd ..
```

## Custom SQL Lambda

```bash
cd lambdas/functions/custom_sql_query

docker buildx build \
  -f Dockerfile \
  --platform linux/amd64 \
  --load \
  -t custom-sql-lambda:latest \
  .

cd -
```

Repeat as required for the remaining services.

---

# Run Snyk Scans

You can use snyk locally to scan your images before pushing them up.

Example:

```bash
snyk container test client:latest \
  --severity-threshold=high \
  --policy-path=.github/.snyk
```

Repeat for each image.

Resolve any High or Critical findings or any other relevant findings before deploying.

---

# AWS Authentication

Authenticate using your breakglass access.

For this to work, you need a management operator or management breakglass permission in your ~/.aws/config

Verify access:

```bash
aws-vault exec management-operator -- aws sts get-caller-identity
```

Verify ECR access:

```bash
aws-vault exec management-operator -- aws ecr describe-repositories --repository-names digideps/client
```

Login to ECR:

```bash
ACCOUNT_ID=<management-account-id>

aws-vault exec management-operator -- aws ecr get-login-password \
  --region eu-west-1 | docker login \
  --username AWS \
  --password-stdin ${ACCOUNT_ID}.dkr.ecr.eu-west-1.amazonaws.com
```

---

# Tag Images For ECR

Set common variables:

```bash
export ACCOUNT_ID=<management-account-id>
export ECR_REGISTRY=${ACCOUNT_ID}.dkr.ecr.eu-west-1.amazonaws.com
export ECR_NAMESPACE=digideps
export IMAGE_TAG=<tag-to-use>
```

Example for client:

```bash
docker tag client:latest \
  ${ECR_REGISTRY}/${ECR_NAMESPACE}/client:${IMAGE_TAG}
```

Example for api:

```bash
docker tag api:latest \
  ${ECR_REGISTRY}/${ECR_NAMESPACE}/api:${IMAGE_TAG}
```

---

# Push Images To ECR

Client:

```bash
docker push \
  ${ECR_REGISTRY}/${ECR_NAMESPACE}/client:${IMAGE_TAG}
```

API:

```bash
docker push \
  ${ECR_REGISTRY}/${ECR_NAMESPACE}/api:${IMAGE_TAG}
```

Push every image that has been built.

---

# Verify Images Exist

Example:

```bash
aws ecr describe-images \
  --repository-name digideps/client \
  --image-ids imageTag=${IMAGE_TAG}
```

Verify the image digest and tag exist before continuing.

---

# Terraform Deployment

We have now built and pushed up all our images based on what is in our local version of the repository.

Next, we need to deploy the new images into AWS using terraform.

## Step 1 - Apply terraform/account (if required)

If changes have been made under:

```text
terraform/account
```

apply these changes before proceeding with environment deployment.

---

## Step 2 - Change To Environment Directory

```bash
cd terraform/environment
```

---

## Step 3 - Set Deployment Variables

Set the Docker image tag (the tag that you tagged your images with earlier):

```bash
export TF_VAR_OPG_DOCKER_TAG=v1.99.999
```

Set the role (for any non dev environment, you will need to be breakglass):

```bash
export TF_VAR_DEFAULT_ROLE=breakglass
```

Set the workspace to what you want to apply it to (always test in non prod first!):

```bash
export TF_WORKSPACE=preproduction
```


Verify:

```bash
echo $TF_VAR_OPG_DOCKER_TAG
echo $TF_VAR_DEFAULT_ROLE
echo $TF_WORKSPACE
```

---

## Step 4 - Initialise Terraform

```bash
terraform init
```

---

## Step 5 - Review The Plan

```bash
terraform plan
```

Carefully review:

* Container image updates
* ECS service updates
* Infrastructure changes
* Security group changes
* Terraform drift

Do not continue until the plan output is understood.

---

## Step 6 - Apply Changes

```bash
terraform apply
```

Confirm when prompted.

---

# Post Deployment Verification

Verify:

* ECS services are healthy
* New tasks are running
* Application URLs respond correctly
* CloudWatch logs are healthy
* No unexpected alarms are firing

This can all be done through the AWS console website and is out of scope for this tutorial.

---

# Recommended Deployment Order

1. Build all Docker images
2. Run Snyk scans
3. Push images to ECR
4. Verify image tags exist
5. Apply terraform/account (if required)
6. Set Terraform variables
7. Run terraform plan
8. Run terraform apply
9. Validate deployment
10. Repeat in production once successfully tested in a non-production environment
