#How to merge migrations

Test

To do after successful production release.

Replace `Version181` in those instruction with the last migration on production.

Run `select max(version) from migrations;` on prod to make sure.

Note: this operations won't affect any environment already at that version 181 or above


* branch into `merge-migrations`
* Take note of the last migration (e.g. 181)
* Delete all the migrations
* Run this from the API container

        sh scripts/initialize_schema.sh
        app/console doctrine:migrations:diff

* Rename the newly created migration into `Version181`
* Run unit tests locally, and also run on jenkins. merge if green
