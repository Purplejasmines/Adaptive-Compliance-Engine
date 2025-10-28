""
Test Redis integration with the application.
Run with: python test_redis_integration.py
"""
import sys
import os
from pathlib import Path

# Add the project root to the Python path
sys.path.append(str(Path(__file__).parent))

from app.core.config import settings
from app.core.redis_manager import redis_manager

def test_redis_connection():
    """Test Redis connection and basic operations"""
    print("\n=== Testing Redis Connection ===")
    
    # Test connection
    try:
        is_connected = redis_manager.ping()
        if is_connected:
            print("✅ Redis connection successful!")
            
            # Test basic operations
            test_key = "test:integration:key"
            test_value = {"status": "success", "message": "Redis is working!"}
            
            # Test set
            redis_manager.set(test_key, test_value, ex=60)  # 60 seconds TTL
            print("✅ Set test key-value pair")
            
            # Test get
            result = redis_manager.get(test_key)
            print(f"✅ Get test value: {result}")
            
            # Test delete
            redis_manager.redis.delete(test_key)
            print("✅ Deleted test key")
            
            return True
            
    except Exception as e:
        print(f"❌ Redis test failed: {e}")
        if hasattr(e, '__traceback__'):
            import traceback
            traceback.print_exc()
    
    return False

if __name__ == "__main__":
    # Print current Redis settings
    print("\n=== Redis Configuration ===")
    print(f"Host: {settings.REDIS_HOST}")
    print(f"Port: {settings.REDIS_PORT}")
    print(f"DB: {settings.REDIS_DB}")
    print(f"Enabled: {settings.REDIS_ENABLED}")
    
    # Run tests
    success = test_redis_connection()
    
    # Print final result
    print("\n=== Test Result ===")
    if success:
        print("✅ All Redis tests passed!")
    else:
        print("❌ Some Redis tests failed")
    
    sys.exit(0 if success else 1)
