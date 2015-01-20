cd /var/www
git clone git@github.com:ministryofjustice/opg-digi-deps-client.git

vi /etc/hosts
127.0.0.1       digideps-client.local


cd /var/www/opg-digi-deps-client
vagrant up


browse at digideps-client.local:8080


vagrant ssh
cd /var/www/opg-digi-deps-client/
php phing.phar build
