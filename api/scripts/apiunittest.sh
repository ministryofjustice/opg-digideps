#!/bin/bash
# Run each folder of unit tests individually. If we were to run them all
# individually it would cause a memory leak.
php bin/phpunit -c tests tests/AppBundle/Controller/
php bin/phpunit -c tests tests/AppBundle/Controller-Report/
php bin/phpunit -c tests tests/AppBundle/Controller-Ndr/
php bin/phpunit -c tests tests/AppBundle/Service/
php bin/phpunit -c tests tests/AppBundle/Entity/
php bin/phpunit -c tests tests/AppBundle/Transformer/
