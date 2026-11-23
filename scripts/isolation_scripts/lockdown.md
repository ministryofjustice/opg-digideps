# Emergency Lockdown

Emergency scripts to quickly block access to various Digideps resources:

## Data Lockdown

- Stopping the Aurora cluster (`digideps-{environment}`)
- Applying an explicit deny policy to the uploads bucket (`pa-uploads-{environment}`)

The original bucket policy is backed up and can be restored.

### Usage

Lock down an environment:

```bash
aws-vault exec digideps-development -- python lockdown.py lockdown production
```

You can run without executing by omitting the --execute flag first.

Reporting Options

## IAM Lockdown

```
aws-vault exec digideps-development -- python iam_lockdown.py report
```

Lock Down Options:

```
aws-vault exec digideps-development -- python iam_lockdown.py lock-role my-role-name
```

```
aws-vault exec digideps-development -- python iam_lockdown.py unlock-role my-role-name
```

```
aws-vault exec digideps-development -- python iam_lockdown.py lock-all
```

```
aws-vault exec digideps-development -- python iam_lockdown.py unlock-all
```

To run them properly, add the execute flag like so:

```
aws-vault exec digideps-development -- python iam_lockdown.py --execute lock-role my-role-name
```
