#!/bin/bash

# Blog Application - Switch Script
# 切換藍綠環境

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Read current active color from nginx config
if grep -q "proxy_pass http://frontend_green" nginx/nginx.conf; then
    CURRENT_COLOR="green"
else
    CURRENT_COLOR="blue"
fi

# Determine target color
if [ -n "$1" ]; then
    TARGET_COLOR="$1"
else
    if [ "$CURRENT_COLOR" = "blue" ]; then
        TARGET_COLOR="green"
    else
        TARGET_COLOR="blue"
    fi
fi

# Validate target color
if [ "$TARGET_COLOR" != "blue" ] && [ "$TARGET_COLOR" != "green" ]; then
    echo "❌ Invalid color: $TARGET_COLOR"
    echo "   Usage: ./scripts/switch.sh [blue|green]"
    exit 1
fi

echo "🔀 Switching Environment"
echo "========================"
echo ""
echo "📍 Current: $CURRENT_COLOR"
echo "🎯 Target:  $TARGET_COLOR"

if [ "$CURRENT_COLOR" = "$TARGET_COLOR" ]; then
    echo ""
    echo "ℹ️  Already on $TARGET_COLOR environment"
    exit 0
fi

echo ""

# Copy the appropriate nginx config
if [ "$TARGET_COLOR" = "green" ]; then
    cp nginx/nginx.green.conf nginx/nginx.conf
else
    cp nginx/nginx.blue.conf nginx/nginx.conf
fi

# Restart nginx to apply changes
echo "🔄 Restarting nginx..."
docker compose restart nginx

echo ""
echo "✅ Switched to $TARGET_COLOR environment!"
echo ""
echo "💡 Current active: $TARGET_COLOR"
