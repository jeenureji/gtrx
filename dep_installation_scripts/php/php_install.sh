#!/bin/bash
function usage()
{
  cat << EOF
  usage: $0 options

  OPTIONS:
    -h  Show this message
    -V  PHP Version to install (Mandatory)
  

EOF
}

function installPhp()
{
  sudo apt-get install -y software-properties-common
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -y
  sudo apt-get install -y php"$phpVersion" php"$phpVersion"-cli php"$phpVersion"-common unzip
  sudo apt-get install -y php"$phpVersion"-curl php"$phpVersion"-gd php"$phpVersion"-mbstring php"$phpVersion"-intl php"$phpVersion"-xml php"$phpVersion"-pgsql php"$phpVersion"-zip php"$phpVersion"-mysql
}

while getopts "hV:" OPTION
do
  case $OPTION in
    V)
      phpVersion=$OPTARG
      ;;
    
  esac
done


installPhp