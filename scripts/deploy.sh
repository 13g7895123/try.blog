#!/bin/bash

# Blog Application - Deploy Script
# 藍綠部署腳本

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Read current active color
source .env 2>/dev/null || true
CURRENT_COLOR="${ACTIVE_COLOR:-blue}"

# Determine target color
if [ "$CURRENT_COLOR" = "blue" ]; then
    TARGET_COLOR="green"
else
    TARGET_COLOR="blue"
fi

echo "🚀 Blue-Green Deployment"
echo "========================"
echo ""
echo "📍 Current: $CURRENT_COLOR"
echo "🎯 Target:  $TARGET_COLOR"
echo ""

# Build and restart the target environment
echo "🔨 Building frontend-$TARGET_COLOR..."
docker compose build "frontend-$TARGET_COLOR"

echo ""
echo "🔄 Restarting frontend-$TARGET_COLOR..."
docker compose up -d "frontend-$TARGET_COLOR"

echo ""
echo "⏳ Waiting for frontend-$TARGET_COLOR to be healthy..."
sleep 10

# Health check
echo ""
echo "🏥 Checking health of frontend-$TARGET_COLOR..."
HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/health/$TARGET_COLOR" 2>/dev/null || echo "000")

if [ "$HEALTH_CHECK" = "200" ]; then
    echo "✅ frontend-$TARGET_COLOR is healthy!"
    echo ""
    
    # Update .env with new active color
    if grep -q "^ACTIVE_COLOR=" .env; then
        sed -i "s/^ACTIVE_COLOR=.*/ACTIVE_COLOR=$TARGET_COLOR/" .env
    else
        echo "ACTIVE_COLOR=$TARGET_COLOR" >> .env
    fi
    
    echo "🔀 Switched active environment to: $TARGET_COLOR"
    echo ""
    echo "✅ Deployment complete!"
    echo ""
    echo "💡 To rollback, run: ./scripts/switch.sh $CURRENT_COLOR"
else
    echo "❌ Health check failed for frontend-$TARGET_COLOR (HTTP $HEALTH_CHECK)"
    echo ""
    echo "⚠️  Deployment aborted. Active environment remains: $CURRENT_COLOR"
    exit 1
fi
