#!/bin/bash
# Deploy script for claude_freelance
# Usage: bash scripts/deploy.sh

set -e

REMOTE_USER="admin"
REMOTE_HOST="50.16.139.240"
REMOTE_PATH="/var/www/html/freelance"
BRANCH="main"

echo "=== Deploying claude_freelance ==="
echo "Target: ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}"
echo ""

# Pull latest code on remote
echo "[1/3] Pulling latest from ${BRANCH}..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "cd ${REMOTE_PATH} && git pull origin ${BRANCH}"

# Run migrations on remote
echo "[2/3] Running migrations..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "cd ${REMOTE_PATH} && php migrations/migrate.php"

# Set permissions
echo "[3/3] Setting permissions..."
ssh ${REMOTE_USER}@${REMOTE_HOST} "chmod -R 755 ${REMOTE_PATH}/public/uploads && chmod -R 755 ${REMOTE_PATH}/storage"

echo ""
echo "=== Deploy complete ==="
echo "Live at: https://freelance.visionquest2020.net"

# Send notification
php C:/xampp/htdocs/claude_messenger/notify.php \
    --subject "claude_freelance deployed" \
    --body "<p>claude_freelance deployed successfully to production.</p><p>Branch: ${BRANCH}</p>" \
    --project claude_freelance 2>/dev/null || true
