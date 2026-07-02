resource "aws_glue_catalog_table" "alb_logs" {
  name          = lower(replace(local.environment, "/[^a-z0-9_]/", "_"))
  database_name = "${var.account.environment.name}_load_balancer_logs"
  owner         = "hadoop"
  parameters = {
    "EXTERNAL"                     = "TRUE"
    "projection.day.format"        = "yyyy/MM/dd"
    "projection.day.interval"      = "1"
    "projection.day.interval.unit" = "DAYS"
    "projection.day.range"         = "2024/01/01,NOW"
    "projection.day.type"          = "date"
    "projection.enabled"           = "true"
    "storage.location.template"    = "s3://alb-logs.${data.aws_region.current.name}.${local.s3_alb_log_account_name}.digideps.opg.justice.gov.uk/${local.environment}/AWSLogs/${var.account.environment.account_id}/elasticloadbalancing/${data.aws_region.current.name}/$${day}"
  }

  table_type = "EXTERNAL_TABLE"

  partition_keys {
    name       = "day"
    parameters = {}
    type       = "string"

  }

  storage_descriptor {
    additional_locations      = []
    bucket_columns            = []
    compressed                = false
    input_format              = "org.apache.hadoop.mapred.TextInputFormat"
    location                  = "s3://alb-logs.${data.aws_region.current.name}.${s3_alb_log_account_name}.digideps.opg.justice.gov.uk/${local.environment}/AWSLogs/${var.account.environment.account_id}/elasticloadbalancing/${data.aws_region.current.name}"
    number_of_buckets         = -1
    output_format             = "org.apache.hadoop.hive.ql.io.HiveIgnoreKeyTextOutputFormat"
    parameters                = {}
    stored_as_sub_directories = false

    columns {
      name       = "type"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "time"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "elb"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "client_ip"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "client_port"
      parameters = {}
      type       = "int"

    }
    columns {
      name       = "target_ip"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "target_port"
      parameters = {}
      type       = "int"

    }
    columns {
      name       = "request_processing_time"
      parameters = {}
      type       = "double"

    }
    columns {
      name       = "target_processing_time"
      parameters = {}
      type       = "double"

    }
    columns {
      name       = "response_processing_time"
      parameters = {}
      type       = "double"

    }
    columns {
      name       = "elb_status_code"
      parameters = {}
      type       = "int"

    }
    columns {
      name       = "target_status_code"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "received_bytes"
      parameters = {}
      type       = "bigint"

    }
    columns {
      name       = "sent_bytes"
      parameters = {}
      type       = "bigint"

    }
    columns {
      name       = "request_verb"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "request_url"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "request_proto"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "user_agent"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "ssl_cipher"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "ssl_protocol"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "target_group_arn"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "trace_id"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "domain_name"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "chosen_cert_arn"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "matched_rule_priority"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "request_creation_time"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "actions_executed"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "redirect_url"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "lambda_error_reason"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "target_port_list"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "target_status_code_list"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "classification"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "classification_reason"
      parameters = {}
      type       = "string"

    }
    columns {
      name       = "conn_trace_id"
      parameters = {}
      type       = "string"

    }

    ser_de_info {
      name = null
      parameters = {
        "input.regex"          = "([^ ]*) ([^ ]*) ([^ ]*) ([^ ]*):([0-9]*) ([^ ]*)[:-]([0-9]*) ([-.0-9]*) ([-.0-9]*) ([-.0-9]*) (|[-0-9]*) (-|[-0-9]*) ([-0-9]*) ([-0-9]*) \"([^ ]*) (.*) (- |[^ ]*)\" \"([^\"]*)\" ([A-Z0-9-_]+) ([A-Za-z0-9.-]*) ([^ ]*) \"([^\"]*)\" \"([^\"]*)\" \"([^\"]*)\" ([-.0-9]*) ([^ ]*) \"([^\"]*)\" \"([^\"]*)\" \"([^ ]*)\" \"([^\\s]+?)\" \"([^\\s]+)\" \"([^ ]*)\" \"([^ ]*)\" ?([^ ]*)? ?( .*)?"
        "serialization.format" = "1"
      }
      serialization_library = "org.apache.hadoop.hive.serde2.RegexSerDe"
    }
  }
}
