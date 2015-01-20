# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.network :forwarded_port, host: 8080, guest: 80
  config.vm.network :forwarded_port, host: 2020, guest: 22
  config.vm.network :forwarded_port, host: 6432, guest: 5432  
 

  config.vm.synced_folder ".", "/var/www/opg-digi-deps-client", id: "vagrant-root",
    owner: "vagrant",
    group: "www-data",
    mount_options: ["dmode=777,fmode=777"]

  config.vm.provider "virtualbox" do |vb|
     vb.customize ["modifyvm", :id, "--memory", "2048"]
  end

  config.vm.provision "puppet" do |puppet|
     puppet.manifests_path = "misc/vagrant/"
     puppet.manifest_file  = "init.pp"
     puppet.options = ['--modulepath=misc/vagrant/puppet/modules']
  end

end
