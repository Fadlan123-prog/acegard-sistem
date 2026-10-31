#!/usr/bin/env bash
# Membuat paket rilis (zip) untuk diunggah lewat FileZilla ke hosting cPanel.
#   ./scripts/build-release.sh            -> build/acegard-app-YYYYMMDD-HHMM.zip   (kode + vendor + aset panel, ~35 MB)
#   ./scripts/build-release.sh --assets   -> juga build/acegard-assets-YYYYMMDD-HHMM.zip (aset situs publik ~165 MB;
#                                            hanya perlu diunggah pada deploy pertama atau bila gambar berubah)
# Aman dijalankan berulang: bekerja di salinan, tidak mengubah vendor/ dan .env lokal.
set -euo pipefail
cd "$(dirname "$0")/.."

OUT="build"
STAMP="$(date +%Y%m%d-%H%M)"
WORK="$OUT/acegard-app"
ZIP="$OUT/acegard-app-$STAMP.zip"

command -v composer >/dev/null || { echo "composer tidak ditemukan"; exit 1; }
command -v rsync    >/dev/null || { echo "rsync tidak ditemukan"; exit 1; }
command -v zip      >/dev/null || { echo "zip tidak ditemukan"; exit 1; }

echo "==> Menyalin kode (tanpa .env, vendor, tests, upload, log)…"
rm -rf "$WORK"; mkdir -p "$WORK"
rsync -a --exclude-from=scripts/release-exclude.txt ./ "$WORK/" --exclude "/$OUT/"

echo "==> Memasang dependensi production (tanpa paket dev)…"
( cd "$WORK" && COMPOSER_PROCESS_TIMEOUT=0 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress -vv )

echo "==> Memastikan folder storage tersedia…"
mkdir -p "$WORK/storage/logs" "$WORK/storage/framework/cache/data" "$WORK/storage/framework/sessions" \
         "$WORK/storage/framework/views" "$WORK/storage/app/public" "$WORK/storage/app/private" "$WORK/bootstrap/cache"
rm -f "$WORK"/bootstrap/cache/*.php

echo "==> Membuat zip…"
rm -f "$OUT"/acegard-app-*.zip
( cd "$OUT" && zip -qr "$(basename "$ZIP")" acegard-app )

if [[ "${1:-}" == "--assets" ]]; then
  echo "==> Membuat zip aset situs publik…"
  rm -f "$OUT"/acegard-assets-*.zip
  ASSETS_ZIP="$OUT/acegard-assets-$STAMP.zip"
  ( cd public && zip -qr "../$ASSETS_ZIP" assets/promotion assets/frontend )
  echo "Aset: $ASSETS_ZIP ($(du -h "$ASSETS_ZIP" | cut -f1)) — ekstrak ke acegard-app/public/"
fi

echo
echo "Selesai: $ZIP ($(du -h "$ZIP" | cut -f1))"
echo "Berikutnya: unggah zip lewat FileZilla, lalu ikuti docs/DEPLOY-FILEZILLA-DOMAINESIA.md"
