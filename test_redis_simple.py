"""
Simple Redis test script
"""
import sys
from pathlib import Path

# Add project root to path
sys.path.append(str(Path(__file__).parent))

from app.core.config import settings
from app.core.redis_manager import redis_manager

def main():
    print("=== Testing Redis Connection ===\n")
    
    # Print Redis config
    print("Redis Configuration:")
    print(f"- Host: {settings.REDIS_HOST}")
    print(f"- Port: {settings.REDIS_PORT}")
    print(f"- DB: {settings.REDIS_DB}")
    print(f"- Enabled: {settings.REDIS_ENABLED}")
    
    if not settings.REDIS_ENABLED:
        print("\n❌ Redis is disabled in settings")
        return False
    
    try:
        # Test connection
        print("\nConnecting to Redis...")
        is_connected = redis_manager.ping()
        
        if not is_connected:
            print("❌ Could not connect to Redis")
            return False
            
        print("✅ Successfully connected to Redis!")
        
        # Test basic operations
        test_key = "test:simple:key"
        test_value = "Hello from Python!"
        
        # Set value
        print(f"\nSetting test key '{test_key}'...")
        redis_manager.set(test_key, test_value, ex=10)  # 10 second TTL
        print("✅ Set test key-value pair")
        
        # Get value
        print(f"\nGetting value for key '{test_key}'...")
        value = redis_manager.get(test_key)
        print(f"✅ Got value: {value}")
        
        # Check TTL
        ttl = redis_manager.redis.ttl(test_key)
        print(f"✅ TTL: {ttl} seconds")
        
        # Delete key
        print(f"\nDeleting test key '{test_key}'...")
        redis_manager.redis.delete(test_key)
        print("✅ Deleted test key")
        
        return True
        
    except Exception as e:
        print(f"\n❌ Error: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    success = main()
    print("\n=== Test Result ===")
    print("✅ All tests passed!" if success else "❌ Tests failed")
    sys.exit(0 if success else 1)
