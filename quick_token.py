#!/usr/bin/env python3
"""
Simple JWT Token Generator for ZRA Dashboard Access
"""

from datetime import datetime, timedelta
import jwt
import hashlib

# ZRA System Configuration (must match your config.py)
SECRET_KEY = "your-secret-key-here"
JWT_ALGORITHM = "HS256"

def create_access_token(data, expires_delta=None):
    """Create JWT access token"""
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=30)

    to_encode.update({"exp": expire})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=JWT_ALGORITHM)

# Generate Admin Token
token_data = {
    "user_id": "admin_user_001",
    "user_type": "admin",
    "username": "admin_user",
    "permissions": [
        "read:dashboard",
        "read:cases",
        "write:cases",
        "read:audit",
        "admin:access"
    ],
    "entity_id": "ENTITY_ADMIN",
    "role": "admin",
    "clearance_level": 3,
    "iat": datetime.utcnow(),
    "iss": "zra-system",
    "aud": "zra-dashboard"
}

token = create_access_token(token_data)

print("🔑 ADMIN JWT TOKEN FOR DASHBOARD ACCESS:")
print("=" * 60)
print(token)
print("=" * 60)
print()
print("📋 HOW TO USE:")
print("1. Copy the token above")
print("2. Access dashboard: http://127.0.0.1:50728/dashboard")
print("3. Add to headers: Authorization: Bearer <token>")
print()
print("🚀 QUICK TEST:")
print(f"curl -H 'Authorization: Bearer {token}' http://127.0.0.1:50728/dashboard")
print()
print("⏰ Token expires in 30 minutes")
print("🔄 Generate new token: python generate_token.py")
