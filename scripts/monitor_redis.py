""
Redis monitoring dashboard.
Run with: python -m scripts.monitor_redis
"""
import time
import json
import sys
from datetime import datetime
from pathlib import Path

# Add the project root to the Python path
sys.path.append(str(Path(__file__).parent.parent))

from app.core.config import settings
from app.core.redis_manager import redis_manager

def get_redis_info():
    """Get Redis server information"""
    if not settings.REDIS_ENABLED:
        return {"error": "Redis is disabled in settings"}
    
    try:
        info = redis_manager.redis.info()
        return {
            "status": "connected",
            "version": info.get("redis_version"),
            "uptime_days": info.get("uptime_in_days"),
            "used_memory_mb": info.get("used_memory") / (1024 * 1024),
            "connected_clients": info.get("connected_clients"),
            "total_commands_processed": info.get("total_commands_processed"),
            "keys_count": sum(1 for _ in redis_manager.redis.scan_iter("*"))
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}

def monitor_redis(interval=5):
    """Monitor Redis with the specified interval in seconds"""
    print("🚀 Redis Monitoring Dashboard\n" + "="*50)
    
    try:
        while True:
            # Clear screen (works on most terminals)
            print("\033[H\033[J", end="")
            
            # Get and display Redis info
            info = get_redis_info()
            print(f"🕒 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
            print("-" * 50)
            
            if "error" in info:
                print(f"❌ Error: {info['error']}")
            else:
                print(f"🔌 Status: {info['status'].upper()}")
                print(f"📊 Version: {info['version']}")
                print(f"⏱️  Uptime: {info['uptime_days']} days")
                print(f"💾 Memory: {info['used_memory_mb']:.2f} MB")
                print(f"👥 Clients: {info['connected_clients']}")
                print(f"📈 Commands: {info['total_commands_processed']}")
                print(f"🔑 Keys: {info['keys_count']}")
                
                # Display some sample keys
                print("\nSample Keys:" + "-" * 40)
                for i, key in enumerate(redis_manager.redis.scan_iter("*", count=5)):
                    key_type = redis_manager.redis.type(key).decode()
                    ttl = redis_manager.redis.ttl(key)
                    print(f"- {key} (Type: {key_type}, TTL: {ttl}s)")
                    if i >= 4:  # Show max 5 keys
                        break
            
            print("\n" + "="*50)
            print("Press Ctrl+C to exit")
            time.sleep(interval)
            
    except KeyboardInterrupt:
        print("\n👋 Exiting Redis monitor")
    except Exception as e:
        print(f"\n❌ Error: {e}")
        if hasattr(e, '__traceback__'):
            import traceback
            traceback.print_exc()

if __name__ == "__main__":
    if not settings.REDIS_ENABLED:
        print("❌ Redis is disabled in settings")
        sys.exit(1)
    
    try:
        monitor_redis(interval=2)  # Update every 2 seconds
    except KeyboardInterrupt:
        print("\n👋 Exiting")
        sys.exit(0)
