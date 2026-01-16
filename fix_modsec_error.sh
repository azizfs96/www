#!/bin/bash
# Script to fix ModSecurity configuration errors
# Run on Linux server as root

echo "=== Fixing ModSecurity Configuration ==="

# 1. Create necessary directories
echo "Creating directories..."
mkdir -p /etc/nginx/modsec
mkdir -p /etc/nginx/modsec/sites
mkdir -p /tmp/modsec

# 2. Create basic modsecurity.conf if it doesn't exist
if [ ! -f /etc/nginx/modsec/modsecurity.conf ]; then
    echo "Creating modsecurity.conf..."
    cat > /etc/nginx/modsec/modsecurity.conf << 'EOF'
# ModSecurity Base Configuration
SecRuleEngine On
SecRequestBodyAccess On
SecResponseBodyAccess Off
SecRequestBodyLimit 13107200
SecRequestBodyNoFilesLimit 131072
SecRequestBodyInMemoryLimit 131072
SecRequestBodyLimitAction Reject

# Audit Log
SecAuditEngine RelevantOnly
SecAuditLogRelevantStatus "^(?:5|4(?!04))"
SecAuditLogParts ABIJDEFHZ
SecAuditLogType Serial
SecAuditLog /var/log/modsec_audit.log

# Arguments
SecArgumentSeparator &
SecCookieFormat 0
SecTmpDir /tmp/modsec/
SecDataDir /tmp/modsec/

# Upload
SecUploadDir /tmp/
SecUploadKeepFiles Off

# Debug (disable in production)
SecDebugLog /var/log/modsec_debug.log
SecDebugLogLevel 0
EOF
fi

# 3. Create global rules file
if [ ! -f /etc/nginx/modsec/global-rules.conf ]; then
    echo "Creating global-rules.conf..."
    cat > /etc/nginx/modsec/global-rules.conf << 'EOF'
# Global WAF Rules
SecRule ARGS|REQUEST_HEADERS "@rx (?i:(?:<script|javascript:|onerror=))" \
    "id:100001,phase:2,deny,status:403,msg:'XSS Attack'"

SecRule ARGS|REQUEST_HEADERS "@rx (?i:(?:union.*select|insert.*into|delete.*from))" \
    "id:100002,phase:2,deny,status:403,msg:'SQL Injection'"
EOF
fi

# 4. Create global IP rules files
touch /etc/nginx/modsec/global-whitelist.txt
touch /etc/nginx/modsec/global-blacklist.txt

# 5. Set permissions
chown -R www-data:www-data /etc/nginx/modsec
chown -R www-data:www-data /tmp/modsec
chmod -R 755 /etc/nginx/modsec

# 6. Create site-specific files for existing sites
for conf in /etc/nginx/sites-enabled/*.waf.conf; do
    if [ -f "$conf" ]; then
        domain=$(basename "$conf" .waf.conf)
        echo "Creating files for: $domain"
        
        # Create ModSecurity config for this domain
        touch /etc/nginx/modsec/${domain}.conf
        touch /etc/nginx/modsec/sites/${domain}-whitelist.txt
        touch /etc/nginx/modsec/sites/${domain}-blacklist.txt
        
        # Add basic content to domain config
        cat > /etc/nginx/modsec/${domain}.conf << EOF
# ModSecurity Configuration for $domain
# Generated automatically

Include /etc/nginx/modsec/modsecurity.conf

# Site-specific rules can be added here
EOF
    fi
done

chown -R www-data:www-data /etc/nginx/modsec

# 7. Test Nginx configuration
echo "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✓ Configuration is valid!"
    echo "Reloading Nginx..."
    systemctl reload nginx
    echo "✓ Done!"
else
    echo "✗ Configuration has errors. Please check the output above."
fi

echo ""
echo "=== Summary ==="
echo "Created:"
echo "  - /etc/nginx/modsec/modsecurity.conf"
echo "  - /etc/nginx/modsec/global-rules.conf"
echo "  - /etc/nginx/modsec/sites/ (site-specific files)"
echo ""
echo "Next steps:"
echo "1. Regenerate site configs from Laravel dashboard"
echo "2. Go to: http://your-domain/waf/sites"
echo "3. Click 'إعادة توليد جميع الملفات'"
