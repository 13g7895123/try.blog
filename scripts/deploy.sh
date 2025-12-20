#!/bin/bash

# Blog Application - Deploy Script
# 藍綠部署腳本 - 部署到非活躍環境，健康檢查後切換

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Determine current active color
if grep -q "proxy_pass http://frontend_green" nginx/nginx.conf; then
    CURRENT_COLOR="green"
    TARGET_COLOR="blue"
else
    CURRENT_COLOR="blue"
    TARGET_COLOR="green"
fi

echo "🚀 Blue-Green Deployment"
echo "========================"
echo ""
echo "📍 Current Active: $CURRENT_COLOR"
echo "🎯 Deploying to:   $TARGET_COLOR"
echo ""

# Build and restart the target environment
echo "🔨 Building frontend-$TARGET_COLOR..."
docker compose build "frontend-$TARGET_COLOR"

echo ""
echo "🔄 Restarting frontend-$TARGET_COLOR..."
docker compose up -d "frontend-$TARGET_COLOR"

echo ""
echo "⏳ Waiting for frontend-$TARGET_COLOR to be ready..."
sleep 15

# Health check
echo ""
echo "🏥 Checking health of frontend-$TARGET_COLOR..."
HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/health/$TARGET_COLOR" 2>/dev/null || echo "000")

if [ "$HEALTH_CHECK" = "200" ]; then
    echo "✅ frontend-$TARGET_COLOR is healthy!"
    echo ""
    
    # Switch nginx to target color
    echo "🔀 Switching nginx to $TARGET_COLOR..."
    cp "nginx/nginx.$TARGET_COLOR.conf" nginx/nginx.conf
    docker compose restart nginx
    
    echo ""
    echo "✅ Deployment complete!"
    echo ""
    echo "📍 Active environment: $TARGET_COLOR"
    echo ""
    echo "💡 To rollback, run: ./scripts/rollback.sh"
else
    echo "❌ Health check failed for frontend-$TARGET_COLOR (HTTP $HEALTH_CHECK)"
    echo ""
    echo "⚠️  Deployment aborted. Active environment remains: $CURRENT_COLOR"
    echo ""
    echo "🔍 Debug: docker logs blog-frontend-$TARGET_COLOR"
    exit 1
fi
