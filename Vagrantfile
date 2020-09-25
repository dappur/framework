# -*- mode: ruby -*-
# vi: set ft=ruby :
require 'securerandom'

# Globals
httpPort = 8181
mysqlPort = 8381
rootPass = "rootpass"

# Check if settings json file exists or create
if not File.file?('settings.json')
	IO.copy_stream('settings.json.dist', 'settings.json')
end

# Get Settings & Environment
settings = JSON.parse(File.read('settings.json'))
env = settings['environment']

# Update environment db port
settings['db'][env]['port'] = mysqlPort

if settings['cron']['token'].empty?
  settings['cron']['token'] = SecureRandom.uuid.split('-').join
end

File.open("settings.json","w") do |f|
  f.write(JSON.pretty_generate(settings, :indent => "\t"))
end

# Get Database From settings.json
database = settings['db'][env]
dbName = database['database']
dbUser = database['username']
dbPass = database['password']

Vagrant.configure("2") do |config|
    # ubuntu 18.04
  	config.vm.box = "bento/ubuntu-18.04"
    config.vm.box_version = "201912.03.0"
    # forward http port
  	config.vm.network "forwarded_port",
  		guest: httpPort,
  		host: httpPort
    # forward mysql port
  	config.vm.network "forwarded_port",
  		guest: mysqlPort,
  		host: mysqlPort
    # provision
  	config.vm.provision "shell",
  		path: "storage/vagrant/provision.sh",
  		privileged: false,
  		args: [
  			httpPort,
  			rootPass,
  			dbName,
  			dbUser,
  			dbPass,
        mysqlPort,
  		]
    # migrate push command
    config.push.define "migrate", strategy: "local-exec" do |push|
      push.script = "storage/vagrant/push.migrate.sh"
    end
    # rollback push command
    config.push.define "rollback", strategy: "local-exec" do |push|
      push.script = "storage/vagrant/push.rollback.sh"
    end
end