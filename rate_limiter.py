from fastapi import HTTPException, Request
from fastapi.responses import JSONResponse
from functools import wraps
import time
from typing import Callable, Optional

class RateLimiter:
    def __init__(self, redis_manager, limit: int = 100, window: int = 60):
        """
        Initialize rate limiter
        
        Args:
            redis_manager: Instance of RedisManager
            limit: Maximum number of requests allowed in the time window
            window: Time window in seconds
        """
        self.redis = redis_manager
        self.limit = limit
        self.window = window

    def __call__(self, key: Optional[str] = None):
        """
        Decorator to rate limit API endpoints
        
        Args:
            key: Optional custom key for rate limiting. If None, uses client IP
        """
        def decorator(func):
            @wraps(func)
            async def wrapper(request: Request, *args, **kwargs):
                # Use custom key or client IP as default
                rate_key = key or f"rate_limit:{request.client.host}"                
                # Get current timestamp
                current_time = int(time.time())
                
                # Create a pipeline for atomic operations
                pipe = self.redis.redis.pipeline()
                
                # Add current timestamp to sorted set
                pipe.zadd(rate_key, {str(current_time): current_time})
                
                # Remove timestamps older than the time window
                pipe.zremrangebyscore(rate_key, 0, current_time - self.window)
                
                # Get count of requests in current window
                pipe.zcard(rate_key)
                
                # Set expiry on the key
                pipe.expire(rate_key, self.window)
                
                # Execute all commands atomically
                _, _, request_count, _ = pipe.execute()
                
                # Check if rate limit exceeded
                if request_count > self.limit:
                    raise HTTPException(
                        status_code=429,
                        detail={
                            "error": "Too Many Requests",
                            "message": f"Rate limit exceeded. Maximum {self.limit} requests per {self.window} seconds allowed.",
                            "retry_after": self.window - (current_time % self.window)
                        }
                    )
                
                # Call the original function
                return await func(request, *args, **kwargs)
            
            return wrapper
        return decorator
