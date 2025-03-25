## Managing environment variables

### Introduction

Our application is made available across multiple environments, as such we need to pass
in various variables that change depending on the environment we are deploying to.
We do this with environment variables.

### Where to set environment variables

Environment variables can be set in a number of ways.

1) Directly in the container (ie `export MY_VAR=foo`).
We wouldn't normally do this but it is useful to mention and can be used for debugging
2) In .env files (ie frontend.env). They can be loaded in and created as environment variables
in your docker compose file under the heading `env_file`
3) In environment variables in the docker-compose file under the heading `environment`
4) Passed in as command line as part of docker compose run command with `-e`
5) Set as part of terraform task definition under heading `environment`

Please understand that when these are set, what it actually means is that they will be available in
the container. You can see a list of all environment variables available by going into the container
and running `printenv`

### Making variables accessible across your symfony app

Symfony requires environment variables to be loaded in at run time if we want them to be wired into services.

To achieve this, we have a `parameters.yml` file that takes env vars and assigns them to arguments that are then
available to use in the `service.yml`. In turn, this can then inject those variables into different services available in symfony.
As some of the config for the parameters file is dependent on it being available
locally or in AWS (largely for localstack vs real config), we generate the `parameters.yml` at build time using a shell script.

The parameters can either be injected in as arguments for a particular service or can be autowired to be
made available to the whole app under:

```
services:
  _defaults:
    autowire: true
    autoconfigure: true
    bind:
```

### To summarise (with an example)

To summarise all this, if we wanted to add a new variable called SOME_VAR and make it available
in our class SomeThing() in frontend then we would probably want to add to the frontend.env file
and also to the terraform task definition for frontend.

This means the env vars will be set both locally and in AWS. We then need to load it in so we can
treat it as a service level argument so we add it to the shell script (`generate_parameters_yml.sh`)
that generates the `parameters.yml` file

We then use the key from the `parameters.yml` and add it as argument to our SomeThing service in frontend
services.yml as `%some_var%`.

Finally in our SomeThing class we can then instantiate it with the argument of `$some_var` of type string.
