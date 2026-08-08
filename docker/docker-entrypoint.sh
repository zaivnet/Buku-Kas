#!/bin/sh
set -e

echo "🚀 Starting Buku Kas Digital Container..."

# 1. Wait for MySQL/MariaDB database to be ready
if [ -n "$DB_HOST" ]; then
    echo "⌛ Waiting for database at $DB_HOST:$DB_PORT..."
    until nc -z -v -w30 "$DB_HOST" "$DB_PORT"; do
        echo "Waiting for database connection..."
        sleep 2
    done
    echo "✅ Database connection established."
fi

# 2. Ensure Storage Link
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link || true
fi

# 3. Ensure permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# 4. Run Migrations & Seeders automatically
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed database if users table is empty
USER_COUNT=$(php artisan db:table users --count 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ] || [ "$AUTO_SEED" = "true" ]; then
    echo "🌱 Seeding initial database data..."
    php artisan db:seed --force || true
fi

# 5. Production Optimization Caching
echo "⚡ Caching configurations, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Buku Kas Digital is ready!"

# 6. Start Supervisord (PHP-FPM + Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
