#INSTALL

  * Add hosts file line
    
        sudo su
        echo "127.0.0.1       digideps-api.local" >> /etc/hosts

  * Clone repo
 
        cd /var/www
        git clone git@github.com:ministryofjustice/opg-digi-deps-api.git
    
        cd /var/www/opg-digi-deps-api
        vagrant up

  * Build application (only first time)

        vagrant ssh
        cd /var/www/opg-digi-deps-api/
        php phing.phar build

  *  browse at http://digideps-api.local:8081

  * Templates are in 


/app/Resources/views


