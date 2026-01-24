#!/bin/bash

# Nezuko CRM - Post-Compromise Recovery & Security Deployment Script
# Run this on your production server after pulling security updates

set -e

echo "🔒 Nezuko CRM Security Deployment"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Update packages
echo -e "${YELLOW}[1/8]${NC} Installing updated packages..."
composer install --no-dev --optimize-autoloader

# 2. Regenerate APP_KEY
echo -e "${YELLOW}[2/8]${NC} Regenerating APP_KEY for fresh encryption..."
php artisan key:generate

# 3. Clear all caches
echo -e "${YELLOW}[3/8]${NC} Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Run migrations
echo -e "${YELLOW}[4/8]${NC} Running database migrations..."
php artisan migrate --force

# 5. Publish Filament assets
echo -e "${YELLOW}[5/8]${NC} Publishing Filament assets..."
php artisan filament:upgrade

# 6. Run security audit
echo -e "${YELLOW}[6/8]${NC} Running security vulnerability audit..."
if composer audit; then
    echo -e "${GREEN}✓ No vulnerabilities found${NC}"
else
    echo -e "${RED}✗ Vulnerabilities detected - review and update${NC}"
    exit 1
fi

# 7. Set correct permissions
echo -e "${YELLOW}[7/8]${NC} Setting correct directory permissions..."
chmod -R 755 bootstrap/cache storage
chmod -R 775 storage bootstrap/cache

# 8. Optimize for production
echo -e "${YELLOW}[8/8]${NC} Optimizing for production..."
php artisan optimize:clear
php artisan optimize

echo ""
echo -e "${GREEN}✓ Security deployment complete!${NC}"
echo ""
echo "Post-deployment checklist:"
echo "  ☐ Change all database credentials"
echo "  ☐ Rotate API keys (OpenAI, etc.)"
echo "  ☐ Update admin user passwords"
echo "  ☐ Review recent logs: tail -f storage/logs/laravel.log"
echo "  ☐ Test all critical functionality"
echo "  ☐ Verify HTTPS is enforced"
echo "  ☐ Check APP_DEBUG=false in production"
echo ""
echo "Security resources:"
echo "  📖 Read SECURITY.md for hardening guidelines"
echo "  🔐 Run 'composer audit' weekly"
echo "  📋 Set up monitoring/alerting"
echo ""
