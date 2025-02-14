FROM wordpress:php8.2-fpm-alpine

# Tải install-php-extensions và cấp quyền thực thi
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions

# Cài đặt ionCube Loader
RUN install-php-extensions ioncube