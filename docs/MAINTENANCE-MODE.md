# Maintenance mode

Each environment, and each subsite (i.e. admin or frontend), can manually be put into maintenance mode through the AWS console.

Every load balancer is set up with two actions. The default action is to forward all requests to the related ECS service. The other action shows the maintenance page when requests are made to a matching path (by default this is `/dd-maintenance`).

To enable maintenance mode, identify the appropriate load balancer's rules and change the rule for the fixed-response maintenance page so it is enabled for the path `*`. This will forward _all_ requests to that load balancer to the maintenance page.

To disable maintenance mode, return the fixed-response rule for the load balancer to `/dd-maintenance`.
