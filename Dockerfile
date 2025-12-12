# Utiliser PHP 8.4 avec FPM
FROM php:8.4-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    nodejs \
    npm

# Nettoyer le cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de dépendances
COPY composer.json composer.lock package.json package-lock.json ./

# Installer les dépendances PHP (sans dev pour la production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Installer les dépendances NPM
RUN npm ci

# Copier tout le code de l'application
COPY . .

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize

# Build des assets avec Vite
RUN npm run build

# Créer le dossier pour Let's Encrypt
RUN mkdir -p /var/www/public/.well-known/acme-challenge \
    && chown -R www-data:www-data /var/www/public/.well-known

# Configurer les permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copier la configuration Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copier la configuration Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer le fichier de base de données SQLite s'il n'existe pas
RUN touch /var/www/database/database.sqlite \
    && chown www-data:www-data /var/www/database/database.sqlite

# Exposer le port 80
EXPOSE 80

# Script d'entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Démarrer Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
