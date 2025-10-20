FROM public.ecr.aws/amazonlinux/amazonlinux:latest

# NodeのLTSバージョン
ENV NODE_VERSION 18.18.0

COPY ./config/httpd/httpd.conf /etc/httpd/conf/httpd.conf
COPY ./config/php/php.ini /etc/php.ini

RUN mkdir /run/php-fpm

RUN dnf update -y && \
    dnf install -y \
    httpd wget php-fpm php-mysqli php-json php php-devel \
    php-pear php-zip git python3-pip mesa-libGL mesa-libGL-devel \
    mariadb105-server poppler-utils python3-pip qpdf && \
    pip3 install supervisor

# 必要なツールとライブラリをインストール
RUN dnf -y update && \
    dnf -y install \
    gcc \
    make \
    autoconf \
    bison \
    re2c \
    libxml2-devel \
    openssl-devel \
    curl-devel \
    libjpeg-devel \
    libpng-devel \
    freetype-devel \
    gmp-devel \
    oniguruma-devel \
    zlib-devel \
    tar \
    wget \
    libicu-devel \
    libXpm-devel \
    mariadb-connector-c-devel \
    sqlite-devel \
    libzip-devel

RUN dnf clean all

# PHP 8.1のソースを取得してビルド
WORKDIR /usr/src
RUN wget https://www.php.net/distributions/php-8.1.29.tar.gz && \
    tar -xvzf php-8.1.29.tar.gz && \
    cd php-8.1.29 && \
    ./configure --prefix=/usr/local/php \
        --with-config-file-path=/usr/local/php/etc \
        --enable-mbstring \
        --enable-soap \
        --enable-intl \
        --with-openssl \
        --with-curl \
        --with-zlib \
        --with-mysqli \
        --with-pdo-mysql \
        --enable-bcmath \
        --enable-gd \
        --with-jpeg \
        --with-freetype \
        --enable-opcache \
        --with-zip && \
    make -j$(nproc) && \
    make install

RUN rm -rf /usr/src/php-8.1.29* && \
    rm -rf /tmp/* 

# Xdebugのソースを取得
WORKDIR /xdebug
RUN git clone https://github.com/xdebug/xdebug.git . && \
    phpize && \
    ./configure && \
    make && \
    make install

RUN rm -rf /usr/src/xdebug

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
ENV PATH=/usr/local/php/bin:$PATH

RUN python3 -m pip install opencv-python pdf2image

COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
