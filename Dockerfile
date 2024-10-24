# Utilisation de l'image PHP officielle avec Apache
FROM php:8.3-apache

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP nécessaires pour Symfony
RUN docker-php-ext-install \
    intl \
    zip \
    pdo \
    pdo_mysql

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configuration d'Apache
RUN a2enmod rewrite
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Définition du répertoire de travail
WORKDIR /var/www/html

# Configuration des permissions
RUN chown -R www-data:www-data /var/www/html
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Exposition du port 8000
EXPOSE 8000

# Lancement du serveur web
CMD ["apache2-foreground"]