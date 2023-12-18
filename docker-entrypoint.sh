#!/bin/bash

service php7.4-fpm start
service mariadb start
nginx -g 'daemon off;'

