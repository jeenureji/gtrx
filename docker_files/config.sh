#!/bin/bash

service apache2 restart
service postgresql restart
tries=0
pg_isready
while [[ "$?" != "0" && $tries -le 20 ]]
do
	tries=$(( $tries + 1 ))
	echo "Waiting for  postgres to finish loading.....attempt $tries"
	sleep 5
	pg_isready
done
/opt/tomcat/bin/startup.sh
/root/dep_initialization_scripts/postgres/database_initializer.sh -U $dbms_db_user -P $dbms_db_user_pw -D $dbms_db -S $application_db_schema

pushd /root/scripts/db_loader/
php create_db_and_load_data.php /root/scripts/db_loader/sample_json/gtrx_loading.json /root/db_loader_csvs/ localhost 5432 $dbms_db $dbms_db_user $dbms_db_user_pw > /root/db_build_log 2>&1
cp /root/radys-gtrx-prototype.war /opt/tomcat/webapps/ROOT.war
popd
bash