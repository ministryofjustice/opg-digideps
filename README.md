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

        alias gs="git status"
        alias gdc="git diff --cached"
        alias phpunit="/var/www/opg-digi-deps-api/vendor/phpunit/phpunit/phpunit"
        alias behat="/var/www/opg-digi-deps-api/vendor/behat/behat/bin/behat"
        alias phing="/usr/bin/php /var/www/opg-digi-deps-api/phing.phar"
        alias db="sudo -u postgres psql dd"
        cd /var/www/opg-digi-deps-api
        export PS1="vagrant$ "

  * Test
        
        php phing.phar phpunit
  
  * Rest

        See RestInputOuputFormatter class and config from config.yml
        
        Useful links

          * Serialized entities annotations here http://jmsyst.com/libs/serializer/master/reference/annotations
          * http://symfony.com/doc/current/cookbook/service_container/event_listener.html
          * http://symfony.com/doc/current/components/http_kernel/introduction.html



