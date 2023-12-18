FROM debian:bullseye

RUN apt-get update && \
    apt-get -y install php7.4-fpm php7.4-mysqli mariadb-server nginx

COPY setup.sql /

RUN service mariadb start && \
    mysql </setup.sql

COPY nginx-site-fpm /etc/nginx/sites-available/default
COPY docker-entrypoint.sh /

RUN chmod u=rx,go= /docker-entrypoint.sh && \
    touch /var/log/tetris-error.log && \
    chown www-data /var/log/tetris-error.log && \
    chgrp root /var/log/tetris-error.log && \
    chmod ug=rw,o= /var/log/tetris-error.log

ENTRYPOINT /docker-entrypoint.sh

EXPOSE 80
