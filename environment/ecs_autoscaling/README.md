# Simple ECS Autoscaling

This module creates CPU and Memory tracked autoscaling policies for ECS services.

## Providers

| Name | Version |
|------|---------|
| aws  | n/a     |

## Inputs

| Name                                        | Description                                                                                                       | Type     | Default | Required |
|---------------------------------------------|-------------------------------------------------------------------------------------------------------------------|----------|---------|:--------:|
| aws\_ecs\_cluster\_name                     | Name of the ECS cluster for the service being scaled.                                                             | `string` | n/a     |   yes    |
| aws\_ecs\_service\_name                     | Name of the ECS service.                                                                                          | `string` | n/a     |   yes    |
| ecs\_autoscaling\_service\_role\_arn        | The ARN of the IAM role that allows Application AutoScaling to modify your scalable target on your behalf.        | `string` | n/a     |   yes    |
| ecs\_task\_autoscaling\_maximum             | The max capacity of the scalable target.                                                                          | `number` | n/a     |   yes    |
| environment                                 | Name of the environment instance of the online Digideps service.                                                       | `string` | n/a     |   yes    |
| autoscaling\_metric\_track\_cpu\_target     | The target value for the CPU metric.                                                                              | `number` | `80`    |    no    |
| autoscaling\_metric\_track\_memory\_target  | The target value for the memory metric.                                                                           | `number` | `80`    |    no    |
| cpu\_track\_metric\_scale\_in\_cooldown     | The amount of time, in seconds, after a scale in activity completes before another scale in activity can start.   | `number` | `60`    |    no    |
| cpu\_track\_metric\_scale\_out\_cooldown    | The amount of time, in seconds, after a scale-out activity completes before another scale-out activity can start. | `number` | `60`    |    no    |
| ecs\_task\_autoscaling\_minimum             | The min capacity of the scalable target.                                                                          | `number` | `1`     |    no    |
| memory\_track\_metric\_scale\_in\_cooldown  | The amount of time, in seconds, after a scale in activity completes before another scale in activity can start.   | `number` | `60`    |    no    |
| memory\_track\_metric\_scale\_out\_cooldown | The amount of time, in seconds, after a scale-out activity completes before another scale-out activity can start. | `number` | `60`    |    no    |

## Outputs

No output.
