# -*- mode: ruby -*-
# vi: set ft=ruby :

# Define Globals
port = 8181
rootPass = "rootpass"
pmaPass = "phpmyadminpass"

# Check if settings json file exists or create
if not File.file?('settings.json')
	IO.copy_stream('settings.json.dist', 'settings.json')
end

# Get Database From settings.json
settings = JSON.parse(File.read('settings.json'))
env = settings['environment']
database = settings['db'][env]
dbName = database['database']
dbUser = database['username']
dbPass = database['password']

Vagrant.configure("2") do |config|
  	config.vm.box = "bento/ubuntu-18.04"
  	config.vm.network "forwarded_port",
  		guest: port,
  		host: port
  	config.vm.provision "shell",
  		path: "storage/vagrant/provision.sh",
  		privileged: false,
  		args: [
  			port,
  			rootPass,
  			pmaPass,
  			dbName,
  			dbUser,
  			dbPass,
  		]
end