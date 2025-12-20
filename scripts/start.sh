#!/bin/bash

# Blog Application - Start Script
# 啟動所有服務

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

echo "🚀 Starting Blog Application..."
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
fi

# Build and start all services
echo "🔨 Building and starting services..."
docker compose up -d --build

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 10

# Check service status
echo ""
echo "📊 Service Status:"
docker compose ps

echo ""
echo "✅ Blog Application Started!"
echo ""
echo "🌐 Access URLs:"
echo "   Frontend: http://localhost:${NGINX_PORT:-80}"
echo "   Backend:  http://localhost:${BACKEND_PORT:-8080}"
echo "   API:      http://localhost:${NGINX_PORT:-80}/api/"
echo ""
echo "📋 Useful Commands:"
echo "   View logs:    docker compose logs -f"
echo "   Stop:         ./scripts/stop.sh"
echo "   Deploy:       ./scripts/deploy.sh"
echo "   Switch:       ./scripts/switch.sh [blue|green]"
