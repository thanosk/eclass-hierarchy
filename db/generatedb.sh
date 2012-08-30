#!/bin/bash

mysqldump -u hierarchy -phierarchy --opt -t hierarchy \
hierarchy \
course_type \
course \
course_department \
course_is_type \
> data.sql

