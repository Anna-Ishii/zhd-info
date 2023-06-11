FROM public.ecr.aws/amazonlinux/amazonlinux:latest

COPY ./config/httpd/httpd.conf /etc/httpd/conf/httpd.conf

RUN mkdir /run/php-fpm 
# RUN mkdir /var/www/zhd-info-app

RUN dnf update -y && \
    dnf install -y httpd wget php-fpm php-mysqli php-json php php-devel

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" &&\
    php composer-setup.php &&\
    php -r "unlink('composer-setup.php');" &&\
    mv composer.phar /usr/local/bin/composer

# RUN php-fpm 
# ENTRYPOINT ["httpd", "-D", "FOREGROUND"]