#!/bin/bash

# LodgeSubscriptionPlugin Deployment Script
# Usage: ./deploy-plugin.sh

set -e  # Exit on any error

echo "🚀 Starting LodgeSubscriptionPlugin deployment..."

# Configuration
PLUGIN_REPO="https://github.com/halcycon/LodgeSubscriptionPlugin"
PLUGIN_DIR="LodgeSubscriptionBundle"
MAUTIC_ROOT="../../"
WEB_USER="www-data"
WEB_GROUP="www-data"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

echo_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

echo_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Step 1: Remove existing plugin
echo_info "Removing existing plugin directory..."
if [ -d "$PLUGIN_DIR" ]; then
    rm -rf "$PLUGIN_DIR"
    echo_success "Existing plugin removed"
else
    echo_info "No existing plugin found"
fi

# Step 2: Clone fresh copy from GitHub
echo_info "Cloning plugin from GitHub..."
git clone "$PLUGIN_REPO"
echo_success "Plugin cloned successfully"

# Step 3: Rename to match Mautic convention
echo_info "Renaming directory to $PLUGIN_DIR..."
mv LodgeSubscriptionPlugin/ "$PLUGIN_DIR"
echo_success "Directory renamed"

# Step 4: Set proper ownership
echo_info "Setting ownership to $WEB_USER:$WEB_GROUP..."
if command -v sudo >/dev/null 2>&1; then
    sudo chown -R "$WEB_USER:$WEB_GROUP" "$PLUGIN_DIR/"
else
    chown -R "$WEB_USER:$WEB_GROUP" "$PLUGIN_DIR/"
fi
echo_success "Ownership set"

# Step 5: Set proper permissions
echo_info "Setting permissions to 755..."
chmod -R 755 "$PLUGIN_DIR/"
echo_success "Permissions set"

# Step 6: Clear Mautic cache
echo_info "Clearing Mautic cache..."
php "${MAUTIC_ROOT}bin/console" cache:clear --no-debug
echo_success "Cache cleared"

# Step 7: Reload plugins
echo_info "Reloading Mautic plugins..."
php "${MAUTIC_ROOT}bin/console" mautic:plugins:reload
echo_success "Plugins reloaded"

# Step 8: Show current log file
echo_info "Finding current log file..."
LOG_DATE=$(date +%Y-%m-%d)
LOG_FILE="${MAUTIC_ROOT}var/logs/mautic_prod-${LOG_DATE}.php"

echo_success "🎉 Plugin deployment completed successfully!"
echo ""
echo_info "To monitor logs, run:"
echo "tail -f $LOG_FILE"
echo ""
echo_info "Or run this script with --watch-logs to automatically start monitoring:"
echo "./deploy-plugin.sh --watch-logs"

# Optional: Watch logs if requested
if [ "$1" = "--watch-logs" ]; then
    echo ""
    echo_info "Starting log monitoring... (Press Ctrl+C to stop)"
    echo "----------------------------------------"
    tail -f "$LOG_FILE"
fi 