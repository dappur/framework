#!/bin/bash
cd /vagrant
composer install
phinx rollback
