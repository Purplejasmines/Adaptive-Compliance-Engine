import json
import sys
from datetime import datetime, timedelta
from typing import Dict, Any
import jwt
import hashlib

# System Configuration
SECRET_KEY = "your-secret-key-here"  
JWT_ALGORITHM = "HS256"
JWT_EXPIRE_MINUTES = 30

def generate_audit_hash(data: str) -> str:
    """Generate audit hash for data integrity"""
    return hashlib.sha256(data.encode()).hexdigest()

def create_access_token(data: Dict[str, Any], expires_delta: timedelta = None) -> str:
    """Create JWT access token"""
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=JWT_EXPIRE_MINUTES)

    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=JWT_ALGORITHM)
    return encoded_jwt

def generate_test_token(user_type: str = "admin", user_id: str = "test_user_001"):
    """
    Generate a test JWT token for dashboard access

    Args:
        user_type: Type of user (admin, officer, taxpayer, donor)
        user_id: Unique user identifier
    """

    # token payload
    token_data = {
        "user_id": user_id,
        "user_type": user_type,
        "username": f"{user_type}_user",
        "permissions": [
            "read:dashboard",
            "read:cases",
            "write:cases",
            "read:audit",
            "admin:access" if user_type == "admin" else "user:access"
        ],
        "entity_id": f"ENTITY_{user_id}",
        "role": user_type,
        "clearance_level": 3 if user_type == "admin" else 2 if user_type == "officer" else 1,
        "iat": datetime.utcnow(),
        "iss": "zra-system",
        "aud": "zra-dashboard"
    }

    # Generate token
    token = create_access_token(token_data)

    return token

def main():
    print("🔐 ZRA JWT Token Generator")
    print("=" * 50)

    # Get user input
    print("\nSelect user type:")
    print("1. Admin")
    print("2. Officer")
    print("3. Taxpayer")
    print("4. Donor")

    choice = input("\nEnter choice (1-4) [1]: ").strip() or "1"

    user_types = {
        "1": "admin",
        "2": "officer",
        "3": "taxpayer",
        "4": "donor"
    }

    user_type = user_types.get(choice, "admin")

    # Generate user ID
    user_id = f"test_{user_type}_{datetime.now().strftime('%Y%m%d_%H%M%S')}"

    # Generate token
    token = generate_test_token(user_type, user_id)

    print(f"\n✅ Generated token for: {user_type.upper()}")
    print("-" * 50)
    print("TOKEN:")
    print(token)
    print("-" * 50)

    print("\n📋 How to use:")
    print(f"1. Copy the token above")
    print(f"2. Access dashboard: http://127.0.0.1:50728/dashboard")
    print(f"3. Add to request headers: Authorization: Bearer {token}")
    print(f"4. Or use curl: curl -H 'Authorization: Bearer {token}' http://127.0.0.1:50728/dashboard")

    print("\n⚠️  Token expires in 30 minutes!")
    print(f"💡 Run this script again to generate a new token")

if __name__ == "__main__":
    main()
