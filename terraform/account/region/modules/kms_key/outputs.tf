output "kms_key_alias_name" {
  value = aws_kms_alias.main_eu_west_1.name
}

output "eu_west_1_target_key_arn" {
  value = aws_kms_alias.main_eu_west_1.target_key_arn
}

output "eu_west_2_target_key_arn" {
  value = length(aws_kms_alias.main_eu_west_2) > 0 ? aws_kms_alias.main_eu_west_2[0].target_key_arn : null
}

output "eu_west_1_target_key_id" {
  value = aws_kms_alias.main_eu_west_1.target_key_id
}

output "eu_west_2_target_key_id" {
  value = length(aws_kms_alias.main_eu_west_2) > 0 ? aws_kms_alias.main_eu_west_2[0].target_key_id : null
}
