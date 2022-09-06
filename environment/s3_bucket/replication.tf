resource "aws_s3_bucket_replication_configuration" "bucket_replication" {
  depends_on = [aws_s3_bucket_versioning.bucket_versioning]

  role   = var.replication_role_arn
  bucket = aws_s3_bucket.bucket.id

  rule {
    id       = "ReplicationToBackupAccount"
    priority = 1
    status   = var.replication_to_backup ? "Enabled" : "Disabled"


    destination {
      account = var.replication_account_id
      bucket  = var.replication_to_backup_account_bucket

      encryption_configuration {
        replica_kms_key_id = var.replication_kms_key_id
      }

      access_control_translation {
        owner = "Destination"
      }
    }

    delete_marker_replication {
      status = "Enabled"
    }

    filter {}

    source_selection_criteria {
      sse_kms_encrypted_objects {
        status = "Enabled"
      }
    }
  }
  rule {
    id       = "ReplicationWithinAccount"
    priority = 2
    status   = var.replication_within_account ? "Enabled" : "Disabled"


    destination {
      bucket        = var.replication_within_account_bucket
      storage_class = "STANDARD"
    }

    delete_marker_replication {
      status = "Enabled"
    }

    filter {}
  }
}
