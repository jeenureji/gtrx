#!/bin/bash

function usage()
{
  cat << EOF
  usage: $0 options

  OPTIONS:
    -h  Show this message
    -U  Name of database user (mandatory)
    -P  Passowrd for database user (mandatory)
    -D  Database name (mandatory)
    -S 	Schema in the database (optional)
    -d  Path to a dump file to load into the database (optional, but file must exist)
EOF
}


function buildDatabase()
{
	if [[ -z "$dbUser" || -z "$dbPassword" || -z "$databaseName"  ]]
	then
		echo "hehe"
		usage
	else
		echo "Setting up $dbUser postgres user"
		sudo su - postgres -c "createuser -U postgres -d -e -E -l -r -s $dbUser" >/dev/null 2>&1
		if [[ $? -eq 0 ]]
		then
			echo "setting user $dbUser password"
			sudo su - postgres -c "psql -c \"alter user $dbUser with password '$dbPassword';\""
		else
			echo "User $dbUser already exists.....moving on"
		fi
		sudo su - postgres -c "psql -c \"DROP DATABASE IF EXISTS $databaseName;\""
		sudo su - postgres -c "psql -c \"CREATE DATABASE $databaseName WITH OWNER=$dbUser;\""

		if [[ ! -z "$databaseDumpPath" ]]
		then
			PGPASSWORD=$dbPassword psql -U $dbUser -d $databaseName < $databaseDumpPath
		fi

		if [[ ! -z "$databaseSchema" ]]
		then
			PGPASSWORD=$dbPassword psql -U $dbUser -d $databaseName  -c "CREATE SCHEMA IF NOT EXISTS \""$databaseSchema"\""
		fi
	fi
}

while getopts ":U:P:D:S:d:" OPTION
do
	case $OPTION in
		U)
			dbUser=$OPTARG
			;;
		P)
			dbPassword=$OPTARG
			;;
		D)
			databaseName=$OPTARG
			;;
		S)
			databaseSchema=$OPTARG
			;;
		d)
			databaseDumpPath=$OPTARG
			;;
	esac
done

if [[ ! -z $databaseName && $databaseName != "orcl" ]]
then
	buildDatabase
fi