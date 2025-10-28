"""
Initialize Redis with default configuration.
Run with: python -m scripts.init_redis
"""
import json
import sys
from pathlib import Path

# Add project root to path
sys.path.append(str(Path(__file__).parent.parent))

from app.core.config import settings
from app.core.redis_manager import redis_manager

def init_redis():
    print("=== Initializing Redis ===\n")
    
    if not settings.REDIS_ENABLED:
        print("❌ Redis is disabled in settings")
        return False
    
    try:
        # Test connection by accessing the Redis client
        # This will automatically connect and ping the server
        redis_client = redis_manager.redis
        # If we get here, connection was successful
        print(f"✅ Connected to Redis at {settings.REDIS_HOST}:{settings.REDIS_PORT} (DB: {settings.REDIS_DB})")
        
        # Initialize API keys
        api_keys = {
            "admin_key": {
                "role": "admin",
                "permissions": ["read", "write", "delete", "admin"],
                "description": "Admin API key with full access"
            },
            "readonly_key": {
                "role": "viewer",
                "permissions": ["read"],
                "description": "Read-only API key for monitoring"
            }
        }
        
        # Store API keys in Redis
        for key_id, key_data in api_keys.items():
            redis_key = f"api:keys:{key_id}"
            # Convert all values to strings and flatten the dictionary
            redis_data = {}
            for k, v in key_data.items():
                if isinstance(v, (list, dict)):
                    redis_data[k] = json.dumps(v)
                else:
                    redis_data[k] = str(v)
            
            # Use hset instead of hmset (hmset is deprecated)
            if redis_data:
                redis_manager.redis.hset(redis_key, mapping=redis_data)
                redis_manager.redis.expire(redis_key, 30 * 24 * 3600)  # 30 days TTL
                print(f"✅ Initialized API key: {key_id}")
            else:
                print(f"⚠️  No data to store for key: {key_id}")
        
        # Initialize rate limits
        rate_limits = {
            "global": {"limit": 1000, "window": 60},
            "auth": {"limit": 100, "window": 60},
            "api": {"limit": 500, "window": 60}
        }
        
        for scope, limits in rate_limits.items():
            redis_key = f"ratelimit:{scope}"
            # Convert values to strings for storage
            redis_limits = {k: str(v) for k, v in limits.items()}
            redis_manager.redis.hset(redis_key, mapping=redis_limits)
            print(f"✅ Initialized rate limit for {scope}: {limits['limit']} req/{limits['window']}s")
        
        print("\n✅ Redis initialization complete!")
        return True
        
    except Exception as e:
        print(f"\n❌ Error: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    success = init_redis()
    sys.exit(0 if success else 1)
