You can run without executing by omitting the --execute flag first.

Reporting Options

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
