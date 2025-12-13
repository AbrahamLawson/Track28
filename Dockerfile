# ===========================
# Stage 1: Build Frontend Assets
# ===========================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copier les fichiers de dépendances Node
COPY package.json package-lock.json ./

# Installer les dépendances NPM
RUN npm ci --production=false

# Copier les fichiers source
COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js ./

# Build des assets avec Vite
RUN npm run build

# ===========================
# Stage 2: Build PHP Dependencies
# ===========================
FROM composer:latest AS composer-builder

WORKDIR /app

# Copier les fichiers de dépendances Composer
COPY composer.json composer.lock ./

# Installer les dépendances PHP (sans dev pour la production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Copier tout le code pour générer l'autoloader
COPY . .

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize --classmap-authoritative

# ===========================
# Stage 3: Final Production Image
# ===========================
FROM php:8.4-fpm

# Installer les dépendances système minimales
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nginx \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configurer PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le code de l'application
COPY . .

# Copier les dépendances PHP depuis le stage builder
COPY --from=composer-builder /app/vendor ./vendor

# Copier les assets buildés depuis le stage frontend
COPY --from=frontend-builder /app/public/build ./public/build

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
