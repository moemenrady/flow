#!/usr/bin/env bash
set -e

# ==============================================================================
# Flow Project - Local Environment Setup Script for Linux Mint / Ubuntu
# ==============================================================================

echo "=================================================="
echo "🚀 بدء تجهيز بيئة مشروع Flow للتشغيل المحلي..."
echo "=================================================="

# التأكد من تشغيل السكربت بصلاحيات sudo
if [ "$EUID" -ne 0 ]; then
  echo "❌ يرجى تشغيل السكربت بصلاحيات الـ root (sudo):"
  echo "   sudo ./setup.sh"
  exit 1
fi

# تحديد المستخدم العادي ومسار المشروع
REAL_USER=${SUDO_USER:-$(logname)}
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "📂 مسار المشروع: $PROJECT_DIR"
echo "👤 المستخدم: $REAL_USER"

# 1. تحديث مستودعات النظام وتثبيت الأدوات الأساسية
echo ""
echo "📦 [1/7] تحديث مستودعات النظام وتثبيت الأدوات الأساسية..."
apt-get update -y
apt-get install -y git curl wget unzip zip software-properties-common ca-certificates lsb-release

# 2. تثبيت PHP 8.3 والامتدادات المطلوبة لـ Laravel
echo ""
echo "🐘 [2/7] تثبيت PHP والامتدادات اللازمة لـ Laravel 12..."
apt-get install -y php8.3-cli php8.3-common php8.3-mysql php8.3-sqlite3 \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath \
    php8.3-gd php8.3-intl php8.3-readline

# 3. تثبيت Composer عالمياً إذا لم يكن مثبتاً
echo ""
echo "🎼 [3/7] التحقق من تثبيت Composer..."
if ! command -v composer &> /dev/null; then
    echo "⬇️ جاري تحميل وتثبيت Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    chmod +x /usr/local/bin/composer
    echo "✅ تم تثبيت Composer بنجاح."
else
    echo "✅ Composer مثبت بالفعل."
fi

# 4. تثبيت Node.js (v20 LTS) و npm إذا لم يكونا مثبتين
echo ""
echo "🟢 [4/7] التحقق من تثبيت Node.js و npm..."
if ! command -v node &> /dev/null; then
    echo "⬇️ جاري تثبيت Node.js v20 LTS..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
    echo "✅ تم تثبيت Node.js ($(node -v)) و npm ($(npm -v))."
else
    echo "✅ Node.js ($(node -v)) مثبت بالفعل."
fi

# 5. تثبيت MariaDB وإعداد قاعدة البيانات
echo ""
echo "🗄️ [5/7] تثبيت وإعداد خادم قاعدة البيانات MariaDB..."
if ! command -v mariadb &> /dev/null && ! command -v mysql &> /dev/null; then
    apt-get install -y mariadb-server
fi

systemctl enable --now mariadb || systemctl start mariadb || true

echo "⚙️ إنشاء قاعدة البيانات flow_db والمستخدم flow_user..."
mysql -e "CREATE DATABASE IF NOT EXISTS \`flow_db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'flow_user'@'localhost' IDENTIFIED BY 'password123';"
mysql -e "CREATE USER IF NOT EXISTS 'flow_user'@'127.0.0.1' IDENTIFIED BY 'password123';"
mysql -e "GRANT ALL PRIVILEGES ON \`flow_db\`.* TO 'flow_user'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON \`flow_db\`.* TO 'flow_user'@'127.0.0.1';"
mysql -e "FLUSH PRIVILEGES;"
echo "✅ تم إعداد قاعدة البيانات flow_db بنجاح."

# 6. ضبط الصلاحيات للملفات
echo ""
echo "🔒 [6/7] ضبط صلاحيات المشروع والمجلدات..."
chown -R "$REAL_USER:$REAL_USER" "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"

# 7. تثبيت الحزم وتشغيل المايجريشن
echo ""
echo "⚡ [7/7] تثبيت التبعيات وتشغيل المايجريشن والـ Seeders..."

# تشغيل Composer install كمستخدم عادي
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && composer install --no-interaction --prefer-dist --optimize-autoloader"

# تشغيل npm install وبناء الملفات
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && npm install && npm run build"

# تشغيل المايجريشن والـ Seeders
echo "🔄 جاري تشغيل المايجريشن والـ Seeders في قاعدة البيانات..."
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && php artisan migrate:fresh --seed --force"

# تنظيف الكاش والربط
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && php artisan storage:link --force"
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && php artisan config:clear"
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && php artisan cache:clear"
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && php artisan route:clear"
sudo -u "$REAL_USER" bash -c "cd '$PROJECT_DIR' && php artisan view:clear"

echo ""
echo "=================================================="
echo "🎉 مبروك! تم إعداد وتشغيل المشروع بنجاح 🚀"
echo "=================================================="
echo ""
echo "🔑 حسابات تسجيل الدخول التجريبية (Admin):"
echo "   1. الإيميل: dev@gmail.com    | كلمة المرور: Qqwwee332211"
echo "   2. الإيميل: flow3@gmail.com  | كلمة المرور: Emadmario3@"
echo ""
echo "▶️ لتشغيل المشروع في وضع التطوير المحلي:"
echo "   cd $PROJECT_DIR"
echo "   composer run dev"
echo ""
echo "أو في نافذتين منفصلتين:"
echo "   - سيرفر الباك إند: php artisan serve"
echo "   - سيرفر الفرونت إند: npm run dev"
echo "=================================================="
