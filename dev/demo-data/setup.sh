#!/usr/bin/env bash
# Build a throwaway web2project instance loaded with the Cedar Lane sample data.
#
#   ./setup.sh /path/to/web2project      # fresh DB + install + sample data
#   python3 screenshots.py --out ./shots # then capture the docs screenshots
#
# Requirements: PHP 8.x CLI with mysqli/gd/mbstring/xml, MariaDB or MySQL running
# locally, composer, python3 with `requests`, `playwright` and `Pillow`.
# WARNING: drops and recreates the database named in $W2P_DB (default: w2p) and
# deletes includes/config.php and uploaded files in the target checkout.
set -euo pipefail

W2P_DIR="$(cd "${1:?usage: setup.sh /path/to/web2project}" && pwd)"
HERE="$(cd "$(dirname "$0")" && pwd)"
export W2P_DB="${W2P_DB:-w2p}" W2P_DB_USER="${W2P_DB_USER:-w2p}" W2P_DB_PASS="${W2P_DB_PASS:-w2p}"
export W2P_URL="${W2P_URL:-http://127.0.0.1:8080}"
PORT="${W2P_URL##*:}"

echo "== composer dependencies"
(cd "$W2P_DIR" && [ -d vendor ] || composer install --no-dev --no-interaction -q)

echo "== fresh database $W2P_DB"
mysql -e "DROP DATABASE IF EXISTS \`$W2P_DB\`; CREATE DATABASE \`$W2P_DB\` CHARACTER SET utf8;
  CREATE USER IF NOT EXISTS '$W2P_DB_USER'@'localhost' IDENTIFIED BY '$W2P_DB_PASS';
  GRANT ALL ON \`$W2P_DB\`.* TO '$W2P_DB_USER'@'localhost'; FLUSH PRIVILEGES;"
rm -f "$W2P_DIR/includes/config.php"
# uploaded files live in files/<project_id>/; keep the tracked files/temp etc.
find "$W2P_DIR/files" -mindepth 1 -maxdepth 1 -type d -regex '.*/[0-9]+' -exec rm -rf {} +

echo "== PHP built-in server on port $PORT"
pkill -f "php -S 127.0.0.1:$PORT" 2>/dev/null || true
nohup php -S "127.0.0.1:$PORT" -t "$W2P_DIR" > "$HERE/php-server.log" 2>&1 &
sleep 1

cd "$HERE"
python3 seed.py --install
echo "== ready: $W2P_URL  (admin / ${W2P_ADMIN_PASS:-passw0rd}; contractors use buildit2026)"
