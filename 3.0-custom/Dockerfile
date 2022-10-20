FROM php:7.4-apache

# load version info from arguments
ARG release=3.0.2-1
ARG patch=9957

# add helper script
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# install extra packages
RUN apt-get update \
	&& apt-get install -y \
		graphviz \
		mariadb-client \
		unzip \
	&& chmod uga+x /usr/local/bin/install-php-extensions \
	&& install-php-extensions \
		apcu \
		gd \
		ldap \
		mcrypt \
		mysqli \
		soap \
		zip 

# Load iTop files
RUN curl -L -o /tmp/iTop.zip https://sourceforge.net/projects/itop/files/itop/${release}/iTop-${release}-${patch}.zip \
	&& unzip /tmp/iTop.zip -d /tmp/ web/* \
	&& mv -v /tmp/web/* /var/www/html/ \
	&& mkdir /var/www/html/env-production \
	&& chown -R www-data: /var/www/html/ \
	&& rm -rf /tmp/iTop.zip /tmp/web

# Add default configuration
COPY php.ini /usr/local/etc/php/php.ini
COPY config-itop.php /var/www/html/conf/production/config-itop.php
RUN chown -R www-data: /var/www/html/conf

# Add Apache conf
ADD ./apache2.conf /etc/apache2/sites-available/000-default.conf
ADD ./ports.conf /etc/apache2/ports.conf

# Add custom entrypoint
COPY docker-itop-entrypoint /usr/local/bin/
ENTRYPOINT ["docker-itop-entrypoint"]
CMD ["apache2-foreground"]

ENV DB_HOSTNAME="db" DB_PREFIX=""
VOLUME ["/var/www/html/conf", "/var/www/html/data", "/var/www/html/env-production", "/var/www/html/log"]