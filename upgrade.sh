#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# dadCHECKIN-TOO — Upgrade from dadtoo v1
#
# Run this script INSIDE your existing dadtoo directory as root:
#
#   cd /var/www/dadtoo          # or wherever your v1 installation lives
#   bash <(curl -s https://raw.githubusercontent.com/ggreenaz/dadCHECKIN-TOO/master/upgrade.sh)
#
# OR if you already have the file:
#   bash upgrade.sh
# ─────────────────────────────────────────────────────────────────────────────

set -e

DIR="$(pwd)"
REPO="https://github.com/ggreenaz/dadCHECKIN-TOO.git"

echo ""
echo "dadCHECKIN-TOO — Upgrade Script"
echo "================================"
echo "Directory : $DIR"
echo ""

# ── 1. Allow git to run here as root ─────────────────────────────
echo "[ 1/6 ] Configuring git safe directory..."
git config --global --add safe.directory "$DIR"

# ── 2. Init git repo if not already one ──────────────────────────
echo "[ 2/6 ] Initializing git repository..."
git init

# ── 3. Add remote (skip if already set) ──────────────────────────
echo "[ 3/6 ] Setting remote origin..."
if git remote get-url origin &>/dev/null; then
    git remote set-url origin "$REPO"
else
    git remote add origin "$REPO"
fi

# ── 4. Fetch and reset to GitHub master ──────────────────────────
echo "[ 4/6 ] Fetching latest code from GitHub..."
git fetch origin

echo "        Resetting working tree to match GitHub..."
git reset --hard origin/master

# ── 5. Set permissions ────────────────────────────────────────────
echo "[ 5/6 ] Setting file permissions..."
chown -R www-data:www-data .
chmod -R 755 .
chmod 775 config

# ── 6. Done ───────────────────────────────────────────────────────
echo "[ 6/6 ] Done!"
echo ""
echo "  ✓ dadCHECKIN-TOO v2 is ready."
echo ""
echo "  Next step: visit http://yourdomain.com/install"
echo "  The wizard will detect your existing dadtoo database"
echo "  and walk you through the Guided Upgrade."
echo ""
