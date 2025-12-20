#!/bin/bash

# Blog Application - Status Script
# 顯示當前環境狀態

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

# Determine current active color
if grep -q "proxy_pass http://frontend_green" nginx/nginx.conf; then
    CURRENT_COLOR="green"
    OTHER_COLOR="blue"
else
    CURRENT_COLOR="blue"
    OTHER_COLOR="green"
fi

echo "📊 Blog Application Status"
echo "=========================="
echo ""
echo "🎯 Active Environment: $CURRENT_COLOR"
echo "💤 Standby Environment: $OTHER_COLOR"
echo ""
echo "🐳 Container Status:"
docker compose ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
echo ""
echo "🏥 Health Checks:"
BLUE_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/health/blue" 2>/dev/null || echo "000")
GREEN_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/health/green" 2>/dev/null || echo "000")
BACKEND_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/health/backend" 2>/dev/null || echo "000")

echo "   Blue:    HTTP $BLUE_HEALTH"
echo "   Green:   HTTP $GREEN_HEALTH"
echo "   Backend: HTTP $BACKEND_HEALTH"
