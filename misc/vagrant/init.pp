/*
Puppet file for digideps MOJ
Kept very simple, refactor when needed, 
values are hard-coded for simplicify, use grep to align INI and other config settings

come recipes here
http://www.puppetcookbook.com/
*/

exec { "apt-update":
    command => "/usr/bin/apt-get update",
}


Exec["apt-update"] -> Package <| |>


package { [
    "php5-fpm", 
    "php5-curl", 
    "php5-cli", 
    "php5-intl", 
    "php-apc",
    "php5-xdebug",
    "php5-json",
    "curl", 
    "git",
    "nginx",
    "postgresql",
    "postgresql-contrib",
    "php5-pgsql",
    "openssl",
    "build-essential",
    "xorg",
    "libssl-dev",
    "xvfb",
    "poppler-utils",
    "sendmail",
    "ntp",
    "npm",
    "nodejs",
    "wget"  ]: 
   ensure => "installed"
}


service {'nginx':
    ensure => running,
    enable => true,
    require => Package['nginx']
}


# wkhtmltopdf with virtual frame buffer
file {"/usr/local/bin/wkhtmltopdf_vfb":
    ensure  => file,
    content => "xvfb-run -a -s \"-screen 0 640x480x16\" /usr/bin/wkhtmltopdf \"$@\"",
    mode => 755,
}


# phpunit skelgen
exec { "phpunit-skelgen":
    command => "/usr/bin/wget https://phar.phpunit.de/phpunit-skelgen.phar -O /usr/local/bin/phpunit-skelgen && /bin/chmod 755 /usr/local/bin/phpunit-skelgen",
    creates  => "/usr/local/bin/phpunit-skelgen",
    require => Package["wget"]
}


# symlink node to nodejs (otherwise not visible from grunt)
file { '/usr/local/bin/node':
   ensure => 'link',
   target => '/usr/bin/nodejs',
   require => Package["nodejs"]
}


# nginx: remove default site (unlink)
file {"/etc/nginx/sites-enabled/default":
  ensure => absent
}

# nginx: "digideps-api.local" virtualhost
file {"/etc/nginx/sites-enabled/digideps-api.local":
  ensure => file,  
  notify => Service["nginx"],
  require => Package['nginx'],
  content => '#http://stackoverflow.com/questions/19731555/how-to-make-zend-framework-2-work-with-nginx
server {
    listen *:80;

    charset utf-8;

    server_name digideps-api.local;

    index app_dev.php;

    root /var/www/opg-digi-deps-api/web;

    location / {
        # try to serve file directly, fallback to app_dev.php
        try_files $uri/ /app_dev.php;
    }
    
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index app_dev.php;
        include fastcgi_params;

        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    
    location ~ \.htaccess {
        deny all;
    }

    error_log  /var/log/nginx/digideps2-errors.log;
    access_log /var/log/nginx/digideps2-access.log;
}'
}


# apc config
file {"/etc/php5/fpm/conf.d/20-apcu.ini":
    ensure  => file,
    #notify => Service['php5-fpm'],
    require => Package['php5-fpm'],
    content => "extension=apcu.so

apc.enabled=1
apc.num_files_hint = 3000
apc.ttl = 3600
apc.enable_cli = 1
apc.write_lock = 0
apc.shm_size = 32M",
}


# increase xdebug limit, otherwise behat fails
# conf is in /etc/php5/cli/conf.d/20-xdebug.ini wthout PFM
file {"/etc/php5/fpm/conf.d/20-xdebug.ini":
    ensure  => file,
    content => "zend_extension=xdebug.so
xdebug.max_nesting_level = 200",
}


# add #127.0.0.1 digideps-api.local" to /etc/hosts
host { 'digideps-api.local':
   ip => '127.0.0.1',
}

