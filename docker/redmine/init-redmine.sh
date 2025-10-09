#!/bin/bash
# Initialize Redmine with test data for development
# Run this script after Redmine container is up and running

echo "Waiting for Redmine to be ready..."
sleep 10

# The default admin credentials for Redmine
# Username: admin
# Password: admin

echo "============================================"
echo "Redmine Setup Instructions:"
echo "============================================"
echo ""
echo "1. Access Redmine at: http://localhost:3000"
echo "2. Login with default credentials:"
echo "   Username: admin"
echo "   Password: admin"
echo ""
echo "3. First-time setup:"
echo "   - You'll be prompted to change the admin password"
echo "   - Set it to something memorable for development (e.g., 'admin123')"
echo ""
echo "4. Enable REST API:"
echo "   - Go to Administration -> Settings -> API"
echo "   - Check 'Enable REST web service'"
echo "   - Click 'Save'"
echo ""
echo "5. Generate API key for testing:"
echo "   - Go to 'My account' (top right)"
echo "   - Look for 'API access key' in the sidebar"
echo "   - Click 'Show' to reveal your API key"
echo "   - Copy this key to use in your .env file"
echo ""
echo "6. Create test data (optional):"
echo "   - Create a test project"
echo "   - Add some test issues"
echo "   - Log some time entries"
echo ""
echo "============================================"

# Optional: Use curl to check if API is accessible
echo ""
echo "Checking Redmine API status..."
if curl -s http://localhost:3000/users.json 2>/dev/null | grep -q "errors"; then
    echo "✓ Redmine API is responding (authentication required - this is expected)"
else
    echo "⚠ Redmine API may not be ready yet. Please check manually."
fi