#!/usr/bin/env ruby
# frozen_string_literal: true

require 'aws-sdk-core'
require 'aws-sdk-ecs'
require 'aws-sdk-cloudwatchlogs'
require_relative 'file_loader'
require_relative 'aws_config'

if ARGV.length != 2
  puts 'Usage: run_task.rb <service> <task_definition>'
  exit 127
end

CLUSTER_NAME = ENV['TF_WORKSPACE']
SERVICE_NAME = ARGV[0]
TASK_DEFINITION = ARGV[1]
DELAY = 2
MAX_ATTEMPTS = 5000
MAX_TIME = DELAY * MAX_ATTEMPTS

ecs = Aws::ECS::Resource.new.client
cloudwatch = Aws::CloudWatchLogs::Resource.new.client

def get_log_events(cloudwatch, group, stream, next_token = '')
  config = { log_group_name: group,
             log_stream_name: stream,
             start_from_head: true }

  config[:next_token] = next_token unless next_token.empty?

  logs = cloudwatch.get_log_events(config)

  messages = logs[:events].map { |x| x[:message] }
  next_token = logs[:next_forward_token]
  [messages, next_token]
end

def get_task_id(task)
  task[:tasks][0][:task_arn].split('/')[2]
end

def get_stream(ecs, task, container_name)
  [ecs.describe_task_definition(
    task_definition: task[:tasks][0][:task_definition_arn]
  )
      .task_definition
      .container_definitions[0]
      .log_configuration.options['awslogs-stream-prefix'],
   container_name,
   get_task_id(task)].join('/')
end

def get_exit_code(ecs, cluster, task, container_name)
  ecs.describe_tasks(
    tasks: [get_task_id(task)],
    cluster: cluster
  )
     .tasks[0]
     .containers[0]
     .exit_code
end

def get_service_config(ecs, cluster, service)
  ecs.describe_services(
    services: [service],
    cluster: cluster
  )
     .services[0]
end

service = get_service_config(ecs, CLUSTER_NAME, SERVICE_NAME)

# start task
task = ecs.run_task(
  cluster: CLUSTER_NAME,
  task_definition: TASK_DEFINITION,
  count: 1,
  launch_type: 'FARGATE',
  network_configuration: service['network_configuration']
)

CONTAINER_NAME = task[:tasks][0][:containers][0][:name]

# find log stream
logstream = get_stream(ecs, task, CONTAINER_NAME)

# wait for task to start
puts 'Waiting for task to start...'
puts "Log stream : #{logstream}"
ecs.wait_until(:tasks_running,
               { tasks: [get_task_id(task)],
                 cluster: CLUSTER_NAME },
               delay: DELAY,
               max_attempts: MAX_ATTEMPTS)
messages, next_token = get_log_events(cloudwatch, CLUSTER_NAME, logstream)
puts messages

# watch task
ecs.wait_until(:tasks_stopped,
               { tasks: [get_task_id(task)],
                 cluster: CLUSTER_NAME },
               delay: DELAY,
               max_attempts: MAX_ATTEMPTS) do |waiter|
  waiter.before_wait do |_attempts, _response|
    messages, next_token = get_log_events(
      cloudwatch,
      CLUSTER_NAME,
      logstream,
      next_token
    )
    puts messages
  end
end

# catch final output
messages, _next_token = get_log_events(
  cloudwatch,
  CLUSTER_NAME,
  logstream,
  next_token
)
puts messages

# catch exit code
container_exit_code = get_exit_code(ecs, CLUSTER_NAME, task, CONTAINER_NAME)

puts "#{CONTAINER_NAME} container exited with code #{container_exit_code}"
exit container_exit_code
