#INSTALL

  * Add hosts file line
    
        sudo su
        echo "127.0.0.1       digideps-client.local" >> /etc/hosts

 * Clone repo
 
        cd /var/www
        git clone git@github.com:ministryofjustice/opg-digi-deps-client.git
    
        cd /var/www/opg-digi-deps-client
        vagrant up

 * Build application (only first time)

        vagrant ssh
        cd /var/www/opg-digi-deps-client/
        php phing.phar build

 *  browse at http://digideps-client.local:8080

 * Templates are in 

/app/Resources/views


