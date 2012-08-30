#!/bin/bash
. ./create_db_lib.sh

#################
MY_BIN=mysql
MY_HOST=localhost
MY_USER=hierarchy
MY_PASS=hierarchy
#################

upload_sql schema.sql
upload_sql procedures.sql
upload_data data.sql hierarchy
