# Maintenance mode

Each environment, and each subsite (i.e. admin or frontend), can manually be put into maintenance mode through the AWS console.

Every load balancer is set up with two actions. The default action is to forward all requests to the related ECS service. The other action shows the maintenance page when requests are made to a matching path (by default this is `/maintenance`).

### Enabling maintenance mode
- First login to **AWS**
- Then click **EC2** (make sure you are on the **breakglass role**)
- Next go to **Load Balancers**
- Select the relevant environment we want to change the **Load Balancer** for
- Hit the **Listeners** tab and then update the HTTPS setting rule
- Change `maintenance` to `*`

That should be it. To disable maintenance mode, return to the same load balancer and edit the same listener and change the rule back to `/maintenance` from `*`.

We can also use a script to do this:

```
cd scripts/set_maintenance_mode
aws-vault exec identity -- python3 maintenance.py front preproduction on
```
