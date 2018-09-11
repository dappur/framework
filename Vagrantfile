# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-18.04"
  config.vm.network "forwarded_port", guest: 8181, host: 8181
  config.vm.provision "shell", path: "provision.sh", privileged: false
end
