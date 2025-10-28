"""
Simple Redis monitoring script
Run with: python -m scripts.monitor_redis_simple
"""
import time
import sys
from pathlib import Path

# Add project root to path
sys.path.append(str(Path(__file__).parent.parent))

from app.core.config import settings
from app.core.redis_manager import redis_manager

def get_redis_info():
    """Get basic Redis server information"""
    try:
        if not settings.REDIS_ENABLED:
            return {"error": "Redis is disabled in settings"}
            
        info = redis_manager.redis.info()
        return {
            "status": "connected",
            "version": info.get("redis_version", "unknown"),
            "uptime_days": info.get("uptime_in_days", 0),
            "used_memory_mb": info.get("used_memory", 0) / (1024 * 1024),
            "connected_clients": info.get("connected_clients", 0),
            "total_commands": info.get("total_commands_processed", 0),
            "keys_count": len(redis_manager.redis.keys())
        }
    except Exception as e:
        return {"error": str(e)}

def main():
    print("🚀 Redis Monitoring Dashboard\n" + "="*50)
    
    try:
        while True:
            # Clear screen (works on most terminals)
            print("\033[H\033[J", end="")
            
            # Get and display Redis info
            info = get_redis_info()
            print(f"🕒 {time.strftime('%Y-%m-%d %H:%M:%S')}")
            print("-" * 50)
            
            if "error" in info:
                print(f"❌ Error: {info['error']}")
            else:
                print(f"🔌 Status: {info['status'].upper()}")
                print(f"📊 Version: {info['version']}")
                print(f"⏱️  Uptime: {info['uptime_days']} days")
                print(f"💾 Memory: {info['used_memory_mb']:.2f} MB")
                print(f"👥 Clients: {info['connected_clients']}")
                print(f"📈 Commands: {info['total_commands']}")
                print(f"🔑 Total Keys: {info['keys_count']}")
                
                # Display some sample keys
                print("\nSample Keys:" + "-" * 40)
                try:
                    for key in redis_manager.redis.scan_iter(count=5):
                        key_type = redis_manager.redis.type(key)
                        ttl = redis_manager.redis.ttl(key)
                        print(f"- {key} (Type: {key_type}, TTL: {ttl}s)")
                except Exception as e:
                    print(f"  Error listing keys: {e}")
            
            print("\n" + "="*50)
            print("Press Ctrl+C to exit")
            time.sleep(2)  # Update every 2 seconds
            
    except KeyboardInterrupt:
        print("\n👋 Exiting Redis monitor")
    except Exception as e:
        print(f"\n❌ Error: {e}")
        import traceback
        traceback.print_exc()
        return False
    
    return True

if __name__ == "__main__":
    if not settings.REDIS_ENABLED:
        print("❌ Redis is disabled in settings")
        sys.exit(1)
    
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n👋 Exiting")
        sys.exit(0)
