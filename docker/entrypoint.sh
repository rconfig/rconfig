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

# Seed the storage tree from the skeleton baked into the image. A named volume
# gets the image content copied in by Docker on first mount, but a bind mount
# masks it, so an empty host directory would otherwise start with none of the
# application's storage layout and none of the bundled template files.
#
# tar --skip-old-files rather than cp -an. Both leave existing files alone, but
# cp -a also re-applies the skeleton's mode to directories that already exist,
# so an operator who had tightened storage/app/rconfig/data would find it
# widened again on every restart. tar touches only what it creates, and creates
# it with the skeleton's modes rather than the host umask.
STORAGE_DIR=/var/www/html/rconfig/storage
STORAGE_SKELETON=/usr/local/share/rconfig/storage-skeleton

echo "🗂️  Seeding storage from image skeleton..."

# Checked as root, which is what this script runs as. A root owned host
# directory is fine and is handled by the chown below; what this catches is a
# read only mount or an SELinux denial, both of which would otherwise leave the
# container running against a storage tree that was never seeded.
if ! touch "${STORAGE_DIR}/.write-test" 2>/dev/null; then
    echo "ERROR: ${STORAGE_DIR} is not writable inside the container." >&2
    echo "If this is a bind mount, check the host directory is not read only." >&2
    echo "On SELinux hosts (Rocky, RHEL, Fedora) add :z to the volume line," >&2
    echo "for example: - ./storage:/var/www/html/rconfig/storage:z" >&2
    exit 1
fi
rm -f "${STORAGE_DIR}/.write-test"

if [ -d "${STORAGE_SKELETON}" ]; then
    # pipefail is scoped to this subshell so a failure in the reading tar is not
    # hidden by the writing tar exiting cleanly.
    if ! ( set -o pipefail; tar -C "${STORAGE_SKELETON}" -cf - . | tar -C "${STORAGE_DIR}" -xf - --skip-old-files ); then
        echo "ERROR: failed to seed storage from ${STORAGE_SKELETON}." >&2
        exit 1
    fi
fi

# Directories the skeleton cannot carry, because they are gitignored and so are
# absent from the image build context. All of these are also created on demand
# at runtime; creating them here means a fresh container starts with the full
# layout rather than growing it on first use.
mkdir -p \
    /var/www/html/rconfig/storage/framework/cache/data \
    /var/www/html/rconfig/storage/framework/sessions \
    /var/www/html/rconfig/storage/framework/views \
    /var/www/html/rconfig/storage/logs \
    /var/www/html/rconfig/storage/app/public \
    /var/www/html/rconfig/storage/app/rconfig/templates \
    /var/www/html/rconfig/storage/app/rconfig/reports \
    /var/www/html/rconfig/storage/app/rconfig/backups \
    /var/www/html/rconfig/storage/app/rconfig/exports \
    /var/www/html/rconfig/storage/files/uploads

# The config data directory is created apart from the list above. It holds
# downloaded device configs, so its mode is deliberate rather than whatever the
# umask yields, and it is configurable via RCONFIG_CONFIG_DIR_MODE. mkdir -m
# sets the mode only when it creates the directory, so an existing one restored
# from the skeleton or carried over from an older install keeps its own mode.
mkdir -p -m "${RCONFIG_CONFIG_DIR_MODE:-0750}" \
    /var/www/html/rconfig/storage/app/rconfig/data

# Set correct permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html/rconfig/storage
chown -R www-data:www-data /var/www/html/rconfig/bootstrap/cache
# Scoped rather than recursive over all of storage/: storage/app/rconfig/data
# holds downloaded device configs containing device secrets, kept non world
# readable by FileOperations. Recursing here would re-open them every start.
chmod 775 /var/www/html/rconfig/storage
chmod -R 775 /var/www/html/rconfig/storage/framework
chmod -R 775 /var/www/html/rconfig/storage/logs
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
