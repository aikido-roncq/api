#!/bin/bash

if test -f '.env'
then
  exit 0
fi

envExample='.env.example'

while read line
do
  var=$(echo $line | grep "[A-Z_]+" -oP)

  if [[ $var ]]
  then
    echo "$var=${!var}" >> .env
  fi
done < $envExample

cat .env
