FROM public.ecr.aws/amazonlinux/amazonlinux:latest

# NodeのLTSバージョン
ENV NODE_VERSION 18.18.0

COPY ./config/httpd/httpd.conf /etc/httpd/conf/httpd.conf
COPY ./config/php/php.ini /etc/php.ini

RUN mkdir /run/php-fpm 

RUN dnf update -y && \
    dnf install -y httpd wget php-fpm php-mysqli php-json php php-devel php-pear php-zip git python3-pip mesa-libGL mesa-libGL-devel 

RUN git clone https://github.com/xdebug/xdebug.git
RUN cd /xdebug && \
    ./rebuild.sh && \
    ./configure && \
    make

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" &&\
    php composer-setup.php &&\
    php -r "unlink('composer-setup.php');" &&\
    mv composer.phar /usr/local/bin/composer

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
ENV NVM_DIR $HOME/.nvm
RUN . $HOME/.nvm/nvm.sh && \
    nvm install $NODE_VERSION
ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH      $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

RUN python3 -m pip install opencv-python pdf2image