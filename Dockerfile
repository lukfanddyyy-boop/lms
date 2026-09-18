FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN apk add --no-cache nginx supervisor

COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY . /var/www/html

RUN mkdir -p /run/nginx

COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD php-fpm -D && nginx -g "daemon off;"
