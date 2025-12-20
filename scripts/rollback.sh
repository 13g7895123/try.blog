#!/bin/bash

# Blog Application - Rollback Script
# 回滾到上一個環境

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Determine current active color
if grep -q "proxy_pass http://frontend_green" nginx/nginx.conf; then
    CURRENT_COLOR="green"
    ROLLBACK_COLOR="blue"
else
    CURRENT_COLOR="blue"
    ROLLBACK_COLOR="green"
fi

echo "⏪ Rollback"
echo "==========="
echo ""
echo "📍 Current Active: $CURRENT_COLOR"
echo "🔙 Rolling back to: $ROLLBACK_COLOR"
echo ""

# Health check the rollback target first
echo "🏥 Checking health of frontend-$ROLLBACK_COLOR..."
HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/health/$ROLLBACK_COLOR" 2>/dev/null || echo "000")

if [ "$HEALTH_CHECK" = "200" ]; then
    echo "✅ frontend-$ROLLBACK_COLOR is healthy!"
    echo ""
    
    # Switch nginx to rollback color
    echo "🔀 Switching nginx to $ROLLBACK_COLOR..."
    cp "nginx/nginx.$ROLLBACK_COLOR.conf" nginx/nginx.conf
    docker compose restart nginx
    
    echo ""
    echo "✅ Rollback complete!"
    echo ""
    echo "📍 Active environment: $ROLLBACK_COLOR"
else
    echo "❌ frontend-$ROLLBACK_COLOR is not healthy (HTTP $HEALTH_CHECK)"
    echo ""
    echo "⚠️  Cannot rollback. Try restarting the target environment:"
    echo "    docker compose up -d frontend-$ROLLBACK_COLOR"
    exit 1
fi
