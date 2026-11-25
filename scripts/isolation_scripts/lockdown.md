# Emergency Lockdown

Emergency scripts to quickly block access to various Digideps resources:

## Data Lockdown

- Stopping the Aurora cluster (`digideps-{environment}`)
- Applying an explicit deny policy to the uploads bucket (`pa-uploads-{environment}`)

The original bucket policy is backed up and can be restored.

### Usage

Lock down an environment:

```
aws-vault exec digideps-development -- python data_lockdown.py lockdown staging
```

```
aws-vault exec digideps-development -- python data_lockdown.py restore staging
```

You can run without executing by omitting the --execute flag first.

Reporting Options

## IAM Lockdown

Locks down IAM roles

### Usage

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

## Emergency KMS Lockdown

Emergency utility for disabling and re-enabling customer-managed KMS keys.

### Usage

List all KMS keys:

```
aws-vault exec digideps-development -- python kms_lockdown.py report
```

```
aws-vault exec digideps-development -- python kms_lockdown.py lock-all
```

```
aws-vault exec digideps-development -- python kms_lockdown.py unlock-all
```

```
aws-vault exec digideps-development -- python kms_lockdown.py lock-key {key_arn}
```

```
aws-vault exec digideps-development -- python kms_lockdown.py unlock-key {key_arn}
```

## Session Lockdown

Please use the script and follow the instructions in [opg-org-infra](https://github.com/ministryofjustice/opg-org-infra/blob/main/scripts/kill_switches/revoke_session/README.md).
