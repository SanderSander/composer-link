FROM composer:latest

COPY docker-entrypoint.sh /usr/bin/docker-entrypoint.sh

RUN chmod +x /usr/bin/docker-entrypoint.sh && mkdir /composer-link

WORKDIR /composer-link

VOLUME /composer-link

ENTRYPOINT ["/usr/bin/docker-entrypoint.sh"]
