# 🚀 Guide de Déploiement en Production

## ✅ Checklist Pré-Déploiement

### 1. Variables d'Environnement (IMPORTANT!)

Assurez-vous de configurer ces variables dans Dockploy ou votre `.env` de production :

```bash
# Application
APP_NAME="Track28"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://trackk28.com
APP_KEY=base64:VOTRE_CLE_ICI  # Générer avec: php artisan key:generate

# OpenAI (CRITIQUE - L'app ne fonctionnera pas sans cette clé)
OPENAI_API_KEY=sk-...votre_cle_openai...

# Database (SQLite par défaut)
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/database/database.sqlite

# Cache et Sessions
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=error  # En production, logger uniquement les erreurs
```

### 2. Sécurité

✅ **Headers de sécurité** : Configurés dans `docker/nginx.conf`
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy

✅ **HTTPS forcé** : Configuré dans `app/Providers/AppServiceProvider.php`

✅ **Proxy de confiance** : Configuré dans `bootstrap/app.php`

✅ **CSRF Protection** : Activé par défaut dans Laravel

### 3. Optimisations

Le Dockerfile multi-stage build permet :
- ✅ Réduction de la taille de l'image (~60% plus petite)
- ✅ Séparation des builds frontend et backend
- ✅ Pas de dépendances de développement en production
- ✅ Autoloader optimisé avec `--classmap-authoritative`
- ✅ Assets buildés et minifiés

### 4. Fichiers Exclus du Build

Le `.dockerignore` exclut automatiquement :
- Tests et fichiers de test
- Documentation (README.md, CLAUDE.md)
- Fichiers temporaires (logos/, test-scraper.php)
- Configuration locale (docker-compose.yml)
- Fichiers IDE (.idea/, .vscode/)

## 🏗️ Structure Docker Optimisée

### Multi-Stage Build

```
Stage 1: Frontend Builder (Node.js Alpine)
  ↓ Build des assets Vite + Tailwind

Stage 2: Backend Builder (Composer)
  ↓ Installation des dépendances PHP optimisées

Stage 3: Production Image (PHP-FPM)
  ↓ Image finale légère avec uniquement le nécessaire
```

### Avantages
- Image finale : ~200-300MB au lieu de ~600-800MB
- Pas de Node.js ou Composer en production
- Moins de vulnérabilités potentielles
- Démarrage plus rapide

## 📊 Performance

### Optimisations Laravel Automatiques
Le script `entrypoint.sh` applique automatiquement :
- ✅ `php artisan config:cache` - Cache de configuration
- ✅ `php artisan route:cache` - Cache des routes
- ✅ `php artisan view:cache` - Cache des vues Blade

### Optimisations PHP
- ✅ Utilisation de `php.ini-production` (OPcache activé)
- ✅ Autoloader optimisé avec classmap authoritative

## 🔧 Commandes Utiles

### Logs en Production
```bash
# Voir les logs en temps réel
docker logs -f <container_name>

# Voir les logs Laravel
docker exec <container_name> tail -f storage/logs/laravel.log
```

### Maintenance
```bash
# Vider tous les caches
docker exec <container_name> php artisan optimize:clear

# Rebuilder les caches
docker exec <container_name> php artisan optimize

# Exécuter les migrations
docker exec <container_name> php artisan migrate --force
```

### Debug en Production (Temporaire)
```bash
# Activer temporairement le debug (NE PAS LAISSER EN PROD!)
docker exec <container_name> php artisan config:clear
# Puis dans .env : APP_DEBUG=true
# IMPORTANT: Remettre APP_DEBUG=false après debug!
```

## 🚨 Points de Vigilance

### 1. OpenAI API Key
⚠️ **CRITIQUE** : Sans `OPENAI_API_KEY`, l'analyse des concurrents ne fonctionnera pas.
- Vérifier que la clé est valide
- Vérifier les quotas OpenAI
- Monitorer l'utilisation de l'API

### 2. Rate Limiting
L'API OpenAI a des limites de requêtes :
- Implémenter un throttling si beaucoup d'utilisateurs
- Ajouter une gestion de file d'attente pour les analyses

### 3. Scraping des Réseaux Sociaux
Le scraping peut échouer si :
- Les plateformes bloquent l'IP
- Les plateformes changent leur structure HTML
- Les requêtes sont trop fréquentes

### 4. Base de Données SQLite
Pour un trafic important, considérer :
- Migration vers PostgreSQL ou MySQL
- Backups réguliers de `/var/www/database/database.sqlite`

## 📈 Monitoring Recommandé

### Métriques à Surveiller
1. **Temps de réponse API OpenAI** : Devrait être < 10s
2. **Erreurs 500** : Vérifier les logs Laravel
3. **Utilisation CPU/RAM** : Docker stats
4. **Certificat SSL** : Renouvellement automatique Let's Encrypt

### Outils Recommandés
- Laravel Telescope (pour dev/staging uniquement)
- Sentry pour le tracking d'erreurs
- Uptime monitoring (UptimeRobot, Pingdom)

## 🔄 Workflow de Déploiement

1. **Push sur GitHub**
   ```bash
   git add .
   git commit -m "Your changes"
   git push origin main
   ```

2. **Rebuild dans Dockploy**
   - Allez dans l'application
   - Cliquez sur "Rebuild"
   - Attendez la fin du build
   - L'application redémarre automatiquement

3. **Vérifications Post-Déploiement**
   - ✅ Site accessible en HTTPS
   - ✅ Assets chargés correctement
   - ✅ Recherche de concurrents fonctionne
   - ✅ Pas d'erreurs dans la console

## 📝 Notes Importantes

- **Pas de `composer install` en production** : Les dépendances sont buildées dans l'image
- **Pas de `npm install` en production** : Assets déjà buildés
- **Cache persistant** : Les caches Laravel survivent aux redémarrages
- **Logs rotatifs** : Configurer logrotate si beaucoup de logs

## 🆘 Troubleshooting

### Page blanche
1. Vérifier les logs : `docker logs <container>`
2. Vérifier APP_KEY est défini
3. Vérifier permissions : `storage/` et `bootstrap/cache/`

### Mixed Content (HTTP/HTTPS)
1. Vérifier `APP_URL=https://trackk28.com`
2. Vérifier `URL::forceScheme('https')` dans AppServiceProvider

### Let's Encrypt échoue
1. Vérifier DNS pointe vers le bon serveur
2. Vérifier `.well-known/acme-challenge/` est accessible
3. Vérifier Nginx sert les challenges directement

### OpenAI API erreur
1. Vérifier la clé API est valide
2. Vérifier les quotas OpenAI
3. Vérifier la connexion réseau du container

---

**Dernière mise à jour** : 2024-12-13
**Version** : 1.0.0
**Maintenu par** : Track28 Team
