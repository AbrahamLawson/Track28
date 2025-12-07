#!/bin/bash
set -e

echo "🚀 Starting application setup..."

# Attendre que les dépendances soient prêtes
echo "⏳ Waiting for services..."
sleep 2

# Copier le fichier .env si nécessaire
if [ ! -f /var/www/.env ]; then
    echo "📝 Creating .env file..."
    cp /var/www/.env.example /var/www/.env
    php /var/www/artisan key:generate
fi

# Créer le répertoire de base de données s'il n'existe pas
mkdir -p /var/www/database

# Créer la base de données SQLite si elle n'existe pas
if [ ! -f /var/www/database/database.sqlite ]; then
    echo "💾 Creating SQLite database..."
    touch /var/www/database/database.sqlite
fi

# Définir les permissions
chown -R www-data:www-data /var/www/database
chmod -R 775 /var/www/database

# Exécuter les migrations
echo "🔄 Running migrations..."
php /var/www/artisan migrate --force

# Optimiser l'application
echo "⚡ Optimizing application..."
php /var/www/artisan config:cache
php /var/www/artisan route:cache
php /var/www/artisan view:cache

# Créer un lien symbolique pour le storage
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage link..."
    php /var/www/artisan storage:link
fi

echo "✅ Application setup complete!"

# Exécuter la commande passée au conteneur
exec "$@"
