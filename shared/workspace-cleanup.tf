module "workspace-cleanup" {
  source  = "github.com/TomTucka/terraform-workspace-manager/terraform/workspace_cleanup"
  enabled = local.account.name == "development" ? true : false
}
