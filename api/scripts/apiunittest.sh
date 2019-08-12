#!/bin/bash
# Run each folder of unit tests individually. If we were to run them all
# individually it would cause a memory leak.
php bin/phpunit -c tests/phpunit.xml tests/AppBundle/Controller/
php bin/phpunit -c tests/phpunit.xml tests/AppBundle/Controller-Report/
php bin/phpunit -c tests/phpunit.xml tests/AppBundle/Controller-Ndr/
php bin/phpunit -c tests/phpunit.xml tests/AppBundle/Service/
php bin/phpunit -c tests/phpunit.xml tests/AppBundle/Entity/
php bin/phpunit -c tests/phpunit.xml tests/AppBundle/Transformer/
