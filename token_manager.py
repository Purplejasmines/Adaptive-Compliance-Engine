#!/usr/bin/env python3
"""
ZRA Permanent Token Manager
Run this script anytime to generate or retrieve your permanent admin token
"""

import os
import jwt
from datetime import datetime

# ZRA System Configuration
SECRET_KEY = "your-secret-key-here"
JWT_ALGORITHM = "HS256"
TOKEN_FILE = "permanent_token.txt"

def create_permanent_token():
    """Create a permanent JWT token with admin privileges"""
    token_data = {
        'user_id': 'admin_user_permanent',
        'user_type': 'admin',
        'username': 'admin_user',
        'permissions': [
            'read:dashboard', 'read:cases', 'write:cases',
            'read:audit', 'admin:access', 'write:audit',
            'delete:audit', 'manage:users', 'system:config'
        ],
        'entity_id': 'ENTITY_ADMIN_PERMANENT',
        'role': 'admin',
        'clearance_level': 5,
        'iat': datetime.utcnow(),
        'iss': 'zra-system',
        'aud': 'zra-dashboard',
        'permanent': True
    }

    return jwt.encode(token_data, SECRET_KEY, algorithm=JWT_ALGORITHM)

def save_token(token):
    """Save token to file"""
    with open(TOKEN_FILE, 'w') as f:
        f.write("🔑 ZRA PERMANENT ADMIN TOKEN (NEVER EXPIRES)\n")
        f.write("=" * 60 + "\n")
        f.write(token + "\n")
        f.write("=" * 60 + "\n")
        f.write(f"\n📋 Usage: Authorization: Bearer {token}\n")
        f.write(f"🚀 Dashboard: http://127.0.0.1:8000/dashboard\n")
        f.write(f"📅 Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")

def main():
    print("🔑 ZRA Permanent Token Manager")
    print("=" * 50)

    # Check if token file exists
    if os.path.exists(TOKEN_FILE):
        print("✅ Token file found!")
        with open(TOKEN_FILE, 'r') as f:
            content = f.read()
            print("📄 Current token file contents:")
            print("-" * 30)
            # Show just the token part
            lines = content.split('\n')
            for line in lines:
                if line.startswith('eyJ'):  # JWT token starts with this
                    print(f"🔑 Token: {line}")
                    break
            print("-" * 30)

        print("\n🔄 Generating fresh token...")
    else:
        print("📄 No token file found, generating new token...")

    token = create_permanent_token()

    # Save to file
    save_token(token)

    print("✅ Fresh permanent token generated!")
    print(f"🔑 Token: {token[:50]}...")
    print()
    print("🚀 QUICK ACCESS:")
    print(f"curl -H 'Authorization: Bearer {token}' http://127.0.0.1:8000/dashboard")
    print()
    print("💡 Run this script anytime you need a fresh token!")
    print("🎯 Your token is saved and ready to use!")

if __name__ == "__main__":
    main()
