* Check out last production tag (https://complete-deputy-report.service.gov.uk/)
* Check what is the latest migration. E.g. 144

* bash into API container 
if using docker-sync, delete Entity directory 

        rm -rf src/AppBundle/Entity/
    
  and wait for re-sync (or launch `docker-sync sync`)
  
        sh scripts/initialize_schema.sh`
        rm -f app/DoctrineMigrations/Version*
        app/console doctrine:migrations:diff
        
* `git checkout .` to un-delete the migrations

    git checkout master
    git checkout -b merge-migrations
    
* delete migrations up to 144 included
* rename (class and file) the previously created migration into Version144
* Build the branch, merge if green

