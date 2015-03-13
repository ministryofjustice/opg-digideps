#Digital deputies (Beta) -  Client

## Local box
Vagrant configuration for Api and Client with instructions and packaged needed:
https://github.com/ministryofjustice/opg-digi-deps-provisioning

## Build

    # build the application (cache clear, db schema creation, PHP syntax check, tests)
    php phing.phar build
    # list other tasks
    php phing.phar -l
    