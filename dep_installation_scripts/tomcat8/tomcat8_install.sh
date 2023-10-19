#!/bin/bash
if [[ ! -e ~/apache-tomcat-8.0.47.tar.gz ]]
then
  sudo apt-get autoremove tomcat7 tomcat7-docs tomcat7-admin tomcat7-examples -y
  sudo apt-get install git -y
  sudo apt-get install ant -y
  wget -P ~/ https://archive.apache.org/dist/tomcat/tomcat-8/v8.0.47/bin/apache-tomcat-8.0.47.tar.gz
  sudo tar -zxvf ~/apache-tomcat-8.0.47.tar.gz -C ~/
  sudo mv ~/apache-tomcat-8.0.47 /opt/tomcat
  export JRE_HOME=/usr/lib/jvm/java-8-openjdk-amd64
  export CATALINA_HOME=/opt/tomcat
  sudo chmod +x $CATALINA_HOME/bin/startup.sh
  sudo chmod +x $CATALINA_HOME/bin/shutdown.sh
  sudo chmod +x $CATALINA_HOME/bin/catalina.sh
  sudo sed -i "s/<\/tomcat-users>/<user username=\"$1\" password=\"$2\" roles=\"manager-gui, manager-status, manager-script, manager-jmx\"\/>\n<\/tomcat-users>/" /opt/tomcat/conf/tomcat-users.xml
  sudo $CATALINA_HOME/bin/startup.sh
fi
