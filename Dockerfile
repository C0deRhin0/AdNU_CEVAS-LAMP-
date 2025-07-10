FROM php:8.1-apache

MAINTAINER Intern <intern@adnu-cevas.local>

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . .

EXPOSE 80