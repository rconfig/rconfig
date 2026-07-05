#!/bin/bash
set -e

echo "=========================================="
echo "  rConfig v8 Core - Starting"
echo "=========================================="

# Wait for database to be ready
echo "⏳ Waiting for database..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done
echo "✅ Database is ready!"

# Create .env if it doesn't exist
if [ ! -f /var/www/html/rconfig/.env ]; then
    echo "📝 Creating .env file from example..."
    cp /var/www/html/rconfig/.env.example /var/www/html/rconfig/.env
fi

# Run composer dump-autoload to complete package discovery (skipped during build)
echo "🔧 Running composer autoload dump..."
cd /var/www/html/rconfig
composer dump-autoload --optimize --no-scripts 2>/dev/null || echo "   (Autoload already optimized)"

if ! grep -q "APP_KEY=base64:" /var/www/html/rconfig/.env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Ensure the storage tree exists before setting permissions. The storage volume
# (or a bind mount) can start empty, and the Laravel directory layout can change
# between image versions, so recreate the expected structure on every start.
echo "🗂️  Ensuring storage directories..."
mkdir -p \
    /var/www/html/rconfig/storage/framework/cache/data \
    /var/www/html/rconfig/storage/framework/sessions \
    /var/www/html/rconfig/storage/framework/views \
    /var/www/html/rconfig/storage/logs \
    /var/www/html/rconfig/storage/app/public

# Set correct permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html/rconfig/storage
chown -R www-data:www-data /var/www/html/rconfig/bootstrap/cache
chmod -R 775 /var/www/html/rconfig/storage
chmod -R 775 /var/www/html/rconfig/bootstrap/cache

# Re-link public/storage to storage/app/public. public/ ships in the image while
# storage/ is a volume, so the symlink must be re-established on every start.
echo "🔗 Linking storage..."
php artisan storage:link --force 2>/dev/null || echo "   (storage link already present)"

# Persist the install marker in storage because the application root is rebuilt
# with the image, while storage is a Docker volume.
INSTALL_MARKER=/var/www/html/rconfig/.installed
PERSISTED_INSTALL_MARKER=/var/www/html/rconfig/storage/.installed

is_database_installed() {
    php <<'PHP'
<?php
$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'rconfig';
$username = getenv('DB_USERNAME') ?: 'rconfig_user';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $count = (int) $pdo->query('select count(*) from migrations')->fetchColumn();
    exit($count > 0 ? 0 : 1);
} catch (Throwable $e) {
    exit(1);
}
PHP
}

if [ -f "$INSTALL_MARKER" ] && [ ! -L "$INSTALL_MARKER" ] && [ ! -f "$PERSISTED_INSTALL_MARKER" ]; then
    echo "💾 Persisting existing installation marker..."
    cp "$INSTALL_MARKER" "$PERSISTED_INSTALL_MARKER"
fi

if [ ! -f "$PERSISTED_INSTALL_MARKER" ] && is_database_installed; then
    echo "💾 Existing installation detected from database."
    touch "$PERSISTED_INSTALL_MARKER"
fi

if [ ! -L "$INSTALL_MARKER" ]; then
    rm -f "$INSTALL_MARKER"
    ln -s "$PERSISTED_INSTALL_MARKER" "$INSTALL_MARKER"
fi

chown -h www-data:www-data "$INSTALL_MARKER"
if [ -f "$PERSISTED_INSTALL_MARKER" ]; then
    chown www-data:www-data "$PERSISTED_INSTALL_MARKER"
fi

if [ -f /var/www/html/rconfig/storage/oauth-private.key ]; then
    echo "🔐 Setting OAuth key permissions..."
    chmod 600 /var/www/html/rconfig/storage/oauth-private.key
    chmod 600 /var/www/html/rconfig/storage/oauth-public.key
    chown www-data:www-data /var/www/html/rconfig/storage/oauth-private.key
    chown www-data:www-data /var/www/html/rconfig/storage/oauth-public.key
fi

# Check if first-time installation
if [ ! -f "$PERSISTED_INSTALL_MARKER" ]; then
    echo "⚠️  First-time installation detected."
    echo "   Please run: docker compose exec app php artisan v8core:install"
else
    echo "🔄 Running migrations..."
    php artisan migrate --force 2>/dev/null || echo "   (No new migrations)"
fi

# Rebuild framework caches on every start. The compiled config, route, and view
# caches live in image space (bootstrap/cache), never in the storage volume, so
# they must be regenerated for each image version and must never persist across
# upgrades. Clear first to drop anything baked into the image, then recache.
echo "🧹 Rebuilding caches..."
php artisan optimize:clear 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🚀 Starting services..."
echo "=========================================="

#Hand off to container CMD (supervisord by default)
exec "$@"
