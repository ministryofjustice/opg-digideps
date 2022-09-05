variable "rules" {
  type = map(object({
    port        = number
    type        = string
    protocol    = string
    target_type = string
    target      = string
  }))
}

variable "name" { type = string }
variable "vpc_id" { type = string }
variable "tags" { type = map(string) }
variable "description" { type = string }
