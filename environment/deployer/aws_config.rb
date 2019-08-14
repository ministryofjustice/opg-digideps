# frozen_string_literal: true

ROLE_NAME = ENV['TF_VAR_default_role'] || 'ci'
TF_WORKSPACE = ENV['TF_WORKSPACE']
CONFIG = hash_from_file('terraform.tfvars.json')
ACCOUNT = CONFIG['accounts'][TF_WORKSPACE] || CONFIG['accounts']['default']
Aws.config[:region]      = 'eu-west-1'
Aws.config[:credentials] = Aws::STS::Resource.new(
  region: 'eu-west-1'
).client.assume_role(
  role_arn: "arn:aws:iam::#{ACCOUNT['account_id']}:role/#{ROLE_NAME}",
  role_session_name: "ruby-#{Time.now.to_i}"
)
