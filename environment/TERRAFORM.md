## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|:----:|:-----:|:-----:|
| OPG\_DOCKER\_TAG | docker tag to deploy | string | n/a | yes |
| account\_ids |  | map | n/a | yes |
| account\_long\_names |  | map | n/a | yes |
| account\_names |  | map | n/a | yes |
| admin\_prefixes |  | map | n/a | yes |
| admin\_whitelists |  | map | n/a | yes |
| domains |  | map | n/a | yes |
| elasticsearch\_count |  | map | n/a | yes |
| email\_domains |  | map | n/a | yes |
| email\_feedback\_addresses |  | map | n/a | yes |
| email\_report\_addresses |  | map | n/a | yes |
| email\_update\_addresses |  | map | n/a | yes |
| external\_certificate\_names |  | map | n/a | yes |
| front\_prefixes |  | map | n/a | yes |
| front\_whitelists |  | map | n/a | yes |
| host\_suffix |  | map | n/a | yes |
| is\_production |  | map | n/a | yes |
| maintenance\_enabled |  | map | n/a | yes |
| max\_instances |  | map | n/a | yes |
| min\_instances |  | map | n/a | yes |
| task\_count |  | map | n/a | yes |
| test\_enabled |  | map | n/a | yes |
| vpc\_enabled |  | map | n/a | yes |
| vpc\_names |  | map | n/a | yes |
| default\_role |  | string | `"ci"` | no |
| management\_role |  | string | `"ci"` | no |

