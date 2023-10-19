#!/bin/bash
sudo apt-get update -y

if [[ -z "$(which psql)" ]]
then
	echo "installing postgres"
	wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
	echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" |sudo tee  /etc/apt/sources.list.d/pgdg.list
	sudo apt-get -y update
	sudo apt -y install postgresql-12 postgresql-client-12
	sudo sed -i "s/local   all             postgres                                peer/local   all             postgres                                     peer\nlocal   all             all                                     md5/" /etc/postgresql/12/main/pg_hba.conf
  	#best to tunnel
  	sudo sed -i "s/host    all             all             127.0.0.1\/32            md5/host    all             all             0.0.0.0\/0            md5/" /etc/postgresql/12/main/pg_hba.conf
  	sudo sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/12/main/postgresql.conf
  	sudo service postgresql restart
fi