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

### Templating with confd

Confd is used for templating out some files with values that are often set in environment variables.
We run confd as part of the start up of our frontend and api apps (see the relevant dockerfiles).
We do this so that whenever we start our app with different environment variables, we regenerate
the templated files with the new variables.

Confd is somewhat odd in that you must map the environment variable to a format like this `/var/name`.
Example: MY_FOO_BAR becomes `/my/foo/bar` in the `.tmpl` file.

If you want to use your env vars in confd then you will find the confd folders under api or client
then `/docker/confd`. These folders hold a conf.d folder and a template folder.

You should add the value in the .toml file under keys (`keys = [ "/my/foo/bar" ]`) and then also in
the .tmpl file as `{{ getv "/my/foo/bar" }}`.

These files are then templated out to the location mentioned in the .toml files under `dest`.

### Making variables accessible across your symfony app

One of the files that gets created with confd is the `parameters.yml` file. This file is of
particular use to our symfony application. It loads parameters that can be injected into
our application through the `services.yml` file.

Although we use confd to template the `parameters.yml` file out, we still use the `parameters.yml.dist`
as part of our build process for the php cache:warmup. As such, you should add any new var to that as well
with some default value (doesn't really matter what the value is).

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
treat it as a service level argument so we add it to the confd toml and tmpl files as `/some/var`
with a key of some_var.

We then use the key from the tmpl file and add it as argument to our SomeThing service in frontend
services.yml as `%some_var%`.

Finally in our SomeThing class we can then instantiate it with the argument of `$some_var` of type string.
