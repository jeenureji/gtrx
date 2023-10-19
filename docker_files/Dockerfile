FROM ubuntu:20.04
RUN apt-get update -y
RUN apt-get install -y locales locales-all wget dos2unix
ENV LC_ALL=C.UTF-8
ENV LANG=C.UTF-8
RUN apt-get install -y sudo nano

ENV dbms_db=gtrx
ENV dbms_db_user=gtrx_admin
ENV dbms_db_user_pw=k8yc10@#
ENV application_db_schema=gtrx_schema
ENV tomcat_app_manager_username=admin
ENV tomcat_app_manager_password=@dm1np@55

COPY dep_installation_scripts /root/dep_installation_scripts
COPY dep_initialization_scripts /root/dep_initialization_scripts
COPY scripts /root/scripts
COPY input_data /root/input_data
RUN apt-get update -y
RUN export DEBIAN_FRONTEND=noninteractive; apt-get install -y tzdata

RUN cd /root/ && find dep_installation_scripts/ -type f -name \"*.sh\" -print0 | xargs -0 dos2unix --
RUN /root/dep_installation_scripts/php/php_install.sh
RUN /root/dep_installation_scripts/apache2/apache2_install.sh
RUN /root/dep_installation_scripts/java1.8/java8_install.sh
RUN /root/dep_installation_scripts/tomcat8/tomcat8_install.sh $tomcat_app_manager_username $tomcat_app_manager_password
RUN /root/dep_installation_scripts/postgres12/postgres_install.sh
RUN mkdir /root/db_loader_csvs
RUN mkdir /data_properties
RUN cd /root/scripts/ && php data_transform.php /root/input_data/RedCAP_dump.csv /root/input_data/GTRx_Meta_Data_Fields.csv /root/input_data/omim_orpha_to_hpo.csv /root/db_loader_csvs/gtrx_diseases.csv /data_properties



COPY docker_files/config.sh /root
COPY docker_files/radys-gtrx-prototype.war /root

RUN chmod 755 /root/config.sh
ENTRYPOINT ["bash" , "-c", "/root/config.sh"]