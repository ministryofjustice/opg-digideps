#INSTALL

  * Add hosts file line
    
        sudo su
        echo "127.0.0.1       digideps-api.local" >> /etc/hosts

  * Clone repo
 
        cd /var/www
        git clone git@github.com:ministryofjustice/opg-digi-deps-api.git
    
        cd /var/www/opg-digi-deps-api
        vagrant up

  * Create database (only first time)
    
        sudo -u postgres psql postgres
        \password postgres
        [123abc]
    
        # create superuser digideps and set password (can be empty)
        sudo -u postgres createuser --superuser digideps
        sudo -u postgres createdb dd_api
        sudo -u postgres createdb dd_api_unit_test
        sudo -u postgres psql
        \password digideps
        [123abc]
        grant all privileges on database dd_api to digideps;
        grant all privileges on database dd_api_unit_test to digideps;

  * Build application (only first time)

        vagrant ssh
        cd /var/www/opg-digi-deps-api/
        php phing.phar build

  *  browse at http://digideps-api.local:8081

  * Useful aliases for the vagrant shell (add to `~/.bashrc`)

        alias phpunit="/var/www/digideps/vendor/phpunit/phpunit/phpunit"
        alias behat="/var/www/digideps/vendor/behat/behat/bin/behat"
        alias phing="/usr/bin/php /var/www/digideps/phing.phar"
        alias db="sudo -u postgres psql dd"
        cd /var/www/opg-digi-deps-api

  * Test

        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"first_name":"Elvis","last_name":"Ciotti","email":"elvis.ciotti@digital.justice.gov.uk"}'  http://digideps-api.local/user/
        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"first_name":"Paul","last_name":"Oforduru","email":"elvis.ciotti@digital.justice.gov.uk"}'  http://digideps-api.local/user/
        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X GET  http://digideps-api.local/user/
        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X GET  http://digideps-api.local/user/2