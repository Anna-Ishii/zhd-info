FROM public.ecr.aws/amazonlinux/amazonlinux:latest

COPY ./config/httpd/httpd.conf /etc/httpd/conf/httpd.conf
COPY ./config/php/php.ini /etc/php.ini

RUN mkdir /run/php-fpm 

RUN dnf update -y && \
    dnf install -y httpd wget php-fpm php-mysqli php-json php php-devel php-pear git python3-pip mesa-libGL mesa-libGL-devel 

RUN git clone https://github.com/xdebug/xdebug.git
RUN cd /xdebug && \
    ./rebuild.sh && \
    ./configure && \
    make

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" &&\
    php composer-setup.php &&\
    php -r "unlink('composer-setup.php');" &&\
    mv composer.phar /usr/local/bin/composer

RUN python3 -m pip install opencv-python pdf2image