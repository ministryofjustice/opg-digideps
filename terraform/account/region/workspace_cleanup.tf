#tfsec:ignore:aws-dynamodb-table-customer-key - no control over this module (to review)
#tfsec:ignore:aws-dynamodb-enable-recovery - not needed, transient data
#tfsec:ignore:aws-dynamodb-enable-at-rest-encryption - not needed, no sensitive data
module "workspace-cleanup" {
  source  = "github.com/TomTucka/terraform-workspace-manager/terraform/workspace_cleanup"
  enabled = var.account.name == "development" ? true : false
}
