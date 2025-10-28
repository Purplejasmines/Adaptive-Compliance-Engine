#!/usr/bin/env python3
"""
Permanent JWT Token Generator for ZRA Dashboard Access
"""

from datetime import datetime, timedelta
import jwt
import hashlib

# ZRA System Configuration (must match your config.py)
SECRET_KEY = "your-secret-key-here"
JWT_ALGORITHM = "HS256"

def create_permanent_token(data):
    """Create JWT access token that never expires"""
    to_encode = data.copy()
    # No expiration time = permanent token
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=JWT_ALGORITHM)
    return encoded_jwt

# Generate Permanent Admin Token
token_data = {
    "user_id": "admin_user_permanent",
    "user_type": "admin",
    "username": "admin_user",
    "permissions": [
        "read:dashboard",
        "read:cases",
        "write:cases",
        "read:audit",
        "admin:access",
        "write:audit",
        "delete:audit",
        "manage:users",
        "system:config"
    ],
    "entity_id": "ENTITY_ADMIN_PERMANENT",
    "role": "admin",
    "clearance_level": 5,  # Maximum clearance
    "iat": datetime.utcnow(),
    "iss": "zra-system",
    "aud": "zra-dashboard",
    "permanent": True  # Custom field to indicate permanent token
}

# Generate the permanent token
token = create_permanent_token(token_data)

print("🔑 PERMANENT ADMIN JWT TOKEN (NEVER EXPIRES):")
print("=" * 80)
print(token)
print("=" * 80)
print()
print("📋 HOW TO USE:")
print("1. Copy the token above")
print("2. Access dashboard: http://127.0.0.1:50728/dashboard")
print("3. Add to headers: Authorization: Bearer <token>")
print()
print("🚀 QUICK TEST:")
print(f"curl -H 'Authorization: Bearer {token}' http://127.0.0.1:50728/dashboard")
print()
print("✅ This token will NEVER expire!")
print("💾 Save this token - you won't need to generate it again")
print()
print("🛡️  SECURITY NOTE:")
print("   This token has maximum admin privileges.")
print("   Store it securely and don't share it!")
