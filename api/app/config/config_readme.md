### Explanation of Different Application Configs

Config is set by the `APP_ENV` variable and can be one of: `local`, `dev`, `test`, or `prod`.

| APP_ENV | Load Behat Files | Use Test DB | JSON Logging |
|---------|------------------|-------------|--------------|
| local   | yes              | no          | no           |
| dev     | yes              | no          | yes          |
| test    | yes              | yes         | yes          |
| prod    | no               | no          | yes          |
