#!/usr/bin/env ruby
# frozen_string_literal: true

require 'aws-sdk-core'
require 'aws-sdk-ecs'
require_relative 'file_loader'
require_relative 'aws_config'

ecs = Aws::ECS::Resource.new.client

CLUSTER = TF_WORKSPACE
SERVICES     = ARGV
DELAY        = 15
MAX_ATTEMPTS = 60
MAX_TIME     = DELAY * MAX_ATTEMPTS

begin
  puts "Waiting upto #{MAX_TIME} seconds for services to stabilise"
  ecs.wait_until(:services_stable,
                 { services: SERVICES,
                   cluster: CLUSTER },
                 delay: DELAY,
                 max_attempts: MAX_ATTEMPTS) do |waiter|
    waiter.before_wait do |attempts, _response|
      puts "Wait #{MAX_TIME - (DELAY * attempts)} more seconds..."
    end
  end
  puts "Success #{SERVICES.join(', ')} stable"
rescue Aws::Waiters::Errors::WaiterFailed
  puts 'FATAL: timeout exceeded, services did not stabilise in time!'
  exit 1
end
