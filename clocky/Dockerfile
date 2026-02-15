FROM php:8.2-apache

# Apache rewrite modul bekapcsolása (nélkülözhetetlen a .htaccess-hez)
RUN a2enmod rewrite

# PHP MySQL kiterjesztés telepítése
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Engedélyezzük az Apache-nak, hogy a .htaccess felülbírhassa az alapbeállításokat
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Munkafelület beállítása
WORKDIR /var/www/html