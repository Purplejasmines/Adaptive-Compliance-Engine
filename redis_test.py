import redis

def test_redis_connection():
    try:
        # Connect to Redis server in WSL
        r = redis.Redis(host='172.22.0.1', port=6379, db=0, socket_connect_timeout=5)
        
        # Test connection
        r.ping()
        print("✅ Successfully connected to Redis!")
        
        # Test basic operations
        r.set('test_key', 'Hello, Redis!')
        value = r.get('test_key')
        print(f"Test value from Redis: {value.decode('utf-8')}")
        
    except Exception as e:
        print(f"❌ Error connecting to Redis: {e}")
        print("Make sure Redis server is running and accessible.")

if __name__ == "__main__":
    test_redis_connection()
