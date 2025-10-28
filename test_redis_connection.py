import redis

def test_connection():
    try:
        # Connect to your WSL Redis server
        r = redis.Redis(
            host='172.22.6.195',  # Your WSL IP
            port=6379,
            socket_connect_timeout=5
        )
        
        # Test the connection
        response = r.ping()
        print(f"✅ Redis connection successful! Response: {response}")
        
        # Test setting and getting a value
        test_key = "test_connection"
        r.set(test_key, "Hello from Python!")
        value = r.get(test_key)
        print(f"Test value from Redis: {value.decode('utf-8')}")
        
    except Exception as e:
        print(f"❌ Redis connection failed: {e}")
        print("Please check:")
        print("1. Is Redis server running in WSL? (run: sudo service redis-server status)")
        print("2. Is the IP address correct? (run: hostname -I in WSL)")
        print("3. Is Redis configured to accept external connections? (check bind in /etc/redis/redis.conf)")
        print("4. Is the port 6379 open in Windows Firewall?")

if __name__ == "__main__":
    test_connection()
