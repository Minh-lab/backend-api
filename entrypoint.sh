#!/bin/sh
# ============================================================
# entrypoint.sh
# Chờ MySQL sẵn sàng rồi mới migrate + serve
# ============================================================

echo " Đang chờ MySQL khởi động..."

MAX_TRIES=30
COUNT=0

# Dùng PDO thay mysqli (PHP CLI chỉ có pdo_mysql)
until php -r "
    try {
        \$dsn = 'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE');
        new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    COUNT=$((COUNT+1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo " MySQL không khởi động được sau 60 giây. Dừng lại."
        exit 1
    fi
    echo "   MySQL chưa sẵn sàng... thử lại ($COUNT/$MAX_TRIES)"
    sleep 2
done

echo "MySQL đã sẵn sàng!"

# Tạo APP_KEY nếu chưa có
php artisan key:generate --ansi 2>/dev/null || true

# Clear cache cũ
php artisan config:clear
php artisan cache:clear

# Migrate
echo "🔄 Đang chạy migrate..."
php artisan migrate --force

# Seed nếu bảng roles còn trống
ROLE_COUNT=$(php artisan tinker --execute="echo \DB::table('roles')->count();" 2>/dev/null | tail -1)
if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
    echo "🌱 Đang seed dữ liệu..."
    php artisan db:seed --force
else
    echo " Dữ liệu đã có, bỏ qua seed."
fi

echo " Khởi động Laravel server..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
