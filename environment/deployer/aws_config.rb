# frozen_string_literal: true

ROLE_NAME = ENV['TF_VAR_default_role'] || 'ci'
TF_WORKSPACE = ENV['TF_WORKSPACE'] || 'development'
CONFIG = hash_from_file('terraform.tfvars')
ACCOUNT_ID = CONFIG['account_ids'][TF_WORKSPACE]

Aws.config[:region]      = 'eu-west-1'
Aws.config[:credentials] = Aws::STS::Resource.new(
  region: 'eu-west-1'
).client.assume_role(
  role_arn: "arn:aws:iam::#{ACCOUNT_ID}:role/#{ROLE_NAME}",
  role_session_name: "ruby-#{Time.now.to_i}"
)
