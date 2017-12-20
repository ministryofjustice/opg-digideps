* Switch to a `merge-migrations` branch
* Find out latest migration on live (e.g. XXX), must be the last one in the repo
* Delete all the migrations files
* from api container

        bash into sh scripts/initialize_schema.sh
        app/console doctrine:migrations:diff
        
        
* Rename last migration into Version XXX where XXX is the laste migration
* Launch on feature branch, merge if green

