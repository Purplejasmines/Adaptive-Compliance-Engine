import redis
from app.core.config import settings

def test_redis_connection():
    try:
        # Create a Redis client
        r = redis.Redis.from_url(
            settings.REDIS_URL,
            socket_connect_timeout=5,
            decode_responses=True
        )
        
        # Test the connection
        r.ping()
        print("✅ Successfully connected to Redis!")
        
        # Test set and get
        test_key = "test_key"
        test_value = "test_value"
        r.set(test_key, test_value)
        assert r.get(test_key) == test_value, "Redis get/set test failed"
        print("✅ Redis get/set operations working correctly")
        
        return True
        
    except Exception as e:
        print(f"❌ Failed to connect to Redis at {settings.REDIS_URL}")
        print(f"Error: {e}")
        print("\nTroubleshooting steps:")
        print("1. Make sure Redis server is running")
        print("2. Check if the Redis URL is correct in your .env file")
        print("3. Verify the Redis port (default: 6379) is not blocked by firewall")
        return False

if __name__ == "__main__":
    test_redis_connection()
