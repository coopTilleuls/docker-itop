FROM supervisions/itop:3.0.2

ADD ./apache2.conf /etc/apache2/sites-available/000-default.conf
ADD ./ports.conf /etc/apache2/ports.conf


# Add custom entrypoint
COPY docker-itop-entrypoint /usr/local/bin/

