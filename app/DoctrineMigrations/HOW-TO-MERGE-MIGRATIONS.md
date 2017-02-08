* Assert production is released to the latest version (e.g 116)
* delete all the migrations
* app/console doctrine:schema:drop --force
* app/console doctrine:migrations:diff
* rename generate migrations into Version<lastest>, e.g. Version116
* run resetdb script and check it goes OK
* run unit tests on the API and check results
* push into a branch, merge if tests pass
* note: changes won't affect staging nor production, as they are already migrated
* Optional: remove old migrations table entry (excluding latest) on prod and staging
* 

