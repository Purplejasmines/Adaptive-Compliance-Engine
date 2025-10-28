""
Initialize Redis with default data and configuration.
Run with: python -m scripts.setup_redis
"""
import sys
import json
from pathlib import Path

# Add the project root to the Python path
sys.path.append(str(Path(__file__).parent.parent))

from app.core.config import settings
from app.core.redis_manager import redis_manager

def initialize_redis():
    """Initialize Redis with default data"""
    print("\n=== Initializing Redis ===")
    
    if not settings.REDIS_ENABLED:
        print("❌ Redis is disabled in settings")
        return False
    
    try:
        # Test connection first
        if not redis_manager.ping():
            print("❌ Could not connect to Redis")
            return False
        
        print("✅ Connected to Redis")
        
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
            redis_manager.redis.hmset(redis_key, key_data)
            # Set TTL of 30 days
            redis_manager.redis.expire(redis_key, 30 * 24 * 3600)
            print(f"✅ Initialized API key: {key_id}")
        
        # Initialize rate limits
        rate_limits = {
            "global": {"limit": 1000, "window": 60},  # 1000 requests per minute
            "auth": {"limit": 100, "window": 60},     # 100 auth requests per minute
            "api": {"limit": 500, "window": 60}       # 500 API requests per minute
        }
        
        for scope, limits in rate_limits.items():
            redis_key = f"ratelimit:{scope}"
            redis_manager.redis.hmset(redis_key, limits)
            print(f"✅ Set rate limit for {scope}: {limits['limit']} req/{limits['window']}s")
        
        print("\n✅ Redis initialization complete!")
        return True
        
    except Exception as e:
        print(f"❌ Redis initialization failed: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    success = initialize_redis()
    sys.exit(0 if success else 1)
