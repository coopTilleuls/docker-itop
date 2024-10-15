FROM php:7.4-apache

# load version info from arguments
ARG release=3.0.2-1
ARG patch=9957

# add helper script
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# install extra packages
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		graphviz \
		mariadb-client \
		unzip \
		cron \ 
		# Security patch
		curl \
		libapr1 \
		libaprutil1 \
		libgssapi-krb5-2 \
		libk5crypto3 \ 
		linux-libc-dev \
		openssl \
		apache2 \
		libc-bin \
		liblua5.3-0 \
		libncurses6 \
		libncursesw6 \
		libnghttp2-14 \
		libtinfo6 \
		ncurses-base \
		ncurses-bin \
		libc-dev-bin \
		bsdutils \ 
		libblkid1 \ 
		libmount1 \ 
		libperl5.32 \
		libsmartcols1 \ 
		libuuid1 \
		libglib2.0-0 \
		mount \
		perl \
		perl-base \
		perl-modules-5.32 \
		util-linux \
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

# Add custom logo
COPY images/* /var/www/html/images/

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Add custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Add default configuration
COPY config-itop.php /var/www/html/conf/production/config-itop.php
RUN chown -R www-data: /var/www/html/conf

# Add Apache conf
ADD ./apache2.conf /etc/apache2/sites-available/000-default.conf
ADD ./ports.conf /etc/apache2/ports.conf
ADD ./robots.txt /var/www/html/robots.txt
# Add crontab config
ADD ./itop-background-task.cron /var/www/html/
#Configure the crontab
RUN crontab -u www-data /var/www/html/itop-background-task.cron
# Add custom entrypoint
COPY docker-itop-entrypoint /usr/local/bin/
ENTRYPOINT ["docker-itop-entrypoint"]
CMD ["apache2-foreground"]

ENV DB_HOSTNAME="db" DB_PREFIX=""
VOLUME ["/var/www/html/conf", "/var/www/html/data", "/var/www/html/env-production", "/var/www/html/log"]
