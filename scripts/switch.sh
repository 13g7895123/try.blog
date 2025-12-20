#!/bin/bash

# Blog Application - Switch Script
# 快速切換藍綠環境

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Read target color from argument or toggle
source .env 2>/dev/null || true
CURRENT_COLOR="${ACTIVE_COLOR:-blue}"

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

# Update .env with new active color
if grep -q "^ACTIVE_COLOR=" .env; then
    sed -i "s/^ACTIVE_COLOR=.*/ACTIVE_COLOR=$TARGET_COLOR/" .env
else
    echo "ACTIVE_COLOR=$TARGET_COLOR" >> .env
fi

echo "✅ Switched to $TARGET_COLOR environment!"
echo ""
echo "💡 Note: The switch takes effect for new requests."
echo "   To reload nginx: docker compose restart nginx"
