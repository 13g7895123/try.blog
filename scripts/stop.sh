#!/bin/bash

# Blog Application - Stop Script
# 停止所有服務

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

echo "🛑 Stopping Blog Application..."
echo ""

docker compose down

echo ""
echo "✅ All services stopped!"
