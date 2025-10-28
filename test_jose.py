#!/usr/bin/env python3
"""
Test script to check jose import
"""

try:
    from jose import JWTError, jwt
    print("✅ jose.JWTError and jwt imported successfully")
except ImportError as e:
    print(f"❌ Import error: {e}")

try:
    import jose
    print(f"✅ jose module found at: {jose.__file__}")
except ImportError as e:
    print(f"❌ jose module import error: {e}")

# Test the specific import from security.py
try:
    from jose import JWTError, jwt
    from passlib.context import CryptContext
    print("✅ All security dependencies imported successfully")
except ImportError as e:
    print(f"❌ Security dependencies import error: {e}")

