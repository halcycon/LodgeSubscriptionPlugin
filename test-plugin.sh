#!/bin/bash

# LodgeSubscriptionPlugin Test Script
# Usage: ./test-plugin.sh [--watch] [--errors-only]

# Configuration
MAUTIC_ROOT="../../"
LOG_DATE=$(date +%Y-%m-%d)
LOG_FILE="${MAUTIC_ROOT}var/logs/mautic_prod-${LOG_DATE}.php"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

echo_test() {
    echo -e "${BLUE}🔍 $1${NC}"
}

echo "🧪 LodgeSubscriptionPlugin Test & Monitor Script"
echo "=============================================="

# Test 1: Check if plugin directory exists
echo_test "Checking plugin directory..."
if [ -d "LodgeSubscriptionBundle" ]; then
    echo_success "Plugin directory exists"
else
    echo_error "Plugin directory not found!"
    exit 1
fi

# Test 2: Check key files
echo_test "Checking key plugin files..."
KEY_FILES=(
    "LodgeSubscriptionBundle/LodgeSubscriptionBundle.php"
    "LodgeSubscriptionBundle/Config/config.php"
    "LodgeSubscriptionBundle/Config/services.php"
    "LodgeSubscriptionBundle/Controller/ReportController.php"
    "LodgeSubscriptionBundle/Controller/WebhookController.php"
)

for file in "${KEY_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo_success "  ✓ $(basename "$file")"
    else
        echo_error "  ✗ $(basename "$file") missing!"
    fi
done

# Test 3: Check PHP syntax
echo_test "Checking PHP syntax..."
if command -v php >/dev/null 2>&1; then
    SYNTAX_ERROR=false
    find LodgeSubscriptionBundle -name "*.php" -exec php -l {} \; > /tmp/syntax_check.log 2>&1
    if grep -q "Parse error\|Fatal error\|syntax error" /tmp/syntax_check.log; then
        echo_error "PHP syntax errors found:"
        cat /tmp/syntax_check.log | grep -E "Parse error|Fatal error|syntax error"
        SYNTAX_ERROR=true
    else
        echo_success "PHP syntax is valid"
    fi
    rm -f /tmp/syntax_check.log
else
    echo_info "PHP not found in PATH, skipping syntax check"
fi

# Test 4: Check if Mautic can detect the plugin
echo_test "Testing Mautic plugin detection..."
PLUGIN_LIST_OUTPUT=$(php "${MAUTIC_ROOT}bin/console" mautic:plugins:list 2>&1)
if echo "$PLUGIN_LIST_OUTPUT" | grep -q "LodgeSubscription"; then
    echo_success "Plugin detected by Mautic"
else
    echo_error "Plugin not detected by Mautic!"
    echo "Output:"
    echo "$PLUGIN_LIST_OUTPUT"
fi

# Test 5: Check recent errors in logs
echo_test "Checking recent errors in logs..."
if [ -f "$LOG_FILE" ]; then
    RECENT_ERRORS=$(tail -50 "$LOG_FILE" | grep -i "lodge\|subscription" | grep -E "ERROR|CRITICAL" | tail -5)
    if [ -n "$RECENT_ERRORS" ]; then
        echo_error "Recent errors found:"
        echo "$RECENT_ERRORS"
    else
        echo_success "No recent Lodge plugin errors found"
    fi
else
    echo_info "Log file not found: $LOG_FILE"
fi

echo ""
echo "=============================================="

# Handle command line options
case "$1" in
    --watch)
        echo_info "Starting log monitoring... (Press Ctrl+C to stop)"
        echo "Watching: $LOG_FILE"
        echo "----------------------------------------"
        tail -f "$LOG_FILE"
        ;;
    --errors-only)
        echo_info "Monitoring errors only... (Press Ctrl+C to stop)"
        echo "Watching: $LOG_FILE"
        echo "----------------------------------------"
        tail -f "$LOG_FILE" | grep --line-buffered -E "ERROR|CRITICAL|EXCEPTION"
        ;;
    *)
        echo_info "Test completed. Available options:"
        echo "  ./test-plugin.sh --watch        # Watch all logs"
        echo "  ./test-plugin.sh --errors-only  # Watch errors only"
        echo ""
        echo_info "Current log file: $LOG_FILE"
        ;;
esac 