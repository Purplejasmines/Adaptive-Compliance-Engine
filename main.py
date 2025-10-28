"""
Zero-Trust Revenue Administration (ZRA) System
Main application entry point
"""

import uvicorn
from datetime import datetime
from fastapi import FastAPI, HTTPException, Request, Response, status, APIRouter, Form, Depends, APIRouter
from fastapi.staticfiles import StaticFiles
from fastapi.responses import HTMLResponse, RedirectResponse
from fastapi.templating import Jinja2Templates
from fastapi.security import OAuth2PasswordRequestForm
from typing import Optional
from fastapi.middleware.cors import CORSMiddleware
from fastapi.middleware.gzip import GZipMiddleware
from starlette.middleware.base import BaseHTTPMiddleware
import logging
from contextlib import asynccontextmanager

from app.core.config import settings
from app.core.database import init_db
from app.api.v1.api import api_router
from app.api.v1.endpoints.admin_router import router as admin_router
from app.core.redis_manager import redis_manager
from app.core.rate_limiter import rate_limiter
from app.core.openapi import configure_openapi, API_TITLE, API_DESCRIPTION, API_VERSION
from app.core.request_validation import setup_request_validation
from app.routers.dashboard import dashboard_router as dash_router

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
)
logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan manager"""
    # Startup
    logger.info("Starting application...")
    
    try:
        # Initialize the database
        logger.info("Initializing database...")
        import asyncio
        await asyncio.to_thread(init_db)
        logger.info("Database initialized successfully")
    except Exception as e:
        logger.error(f"Failed to initialize database: {str(e)}")
        raise

    # Setup monitoring and other services
    # setup_monitoring()  # Uncomment if you have monitoring setup

    yield

    # Shutdown
    logger.info("Shutting down application...")

# Initialize FastAPI app with lifespan and OpenAPI configuration
# Initialize templates with absolute path
import os

# Get the base directory of the application
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
TEMPLATES_DIR = os.path.join(BASE_DIR, "app", "templates")

# Ensure the templates directory exists
os.makedirs(TEMPLATES_DIR, exist_ok=True)

# Initialize templates with explicit auto_reload in development
templates = Jinja2Templates(
    directory=TEMPLATES_DIR,
    auto_reload=True,  # Auto-reload templates in development
    autoescape=True,
)

# Add template debugging information
logger.info(f"\n{'='*80}")
logger.info(f"Template Configuration:")
logger.info(f"- BASE_DIR: {BASE_DIR}")
logger.info(f"- TEMPLATES_DIR: {TEMPLATES_DIR}")
logger.info(f"- Template directory exists: {os.path.exists(TEMPLATES_DIR)}")
logger.info(f"- Files in template directory: {os.listdir(TEMPLATES_DIR) if os.path.exists(TEMPLATES_DIR) else 'Directory not found'}")
logger.info(f"- Current working directory: {os.getcwd()}")
logger.info(f"- Script directory: {os.path.dirname(os.path.abspath(__file__))}")
logger.info(f"{'='*80}\n")


# Add a route to check template existence

app = FastAPI(
    title=API_TITLE,
    description=API_DESCRIPTION,
    version=API_VERSION,
    docs_url="/docs",
    redoc_url="/redoc",
    openapi_url="/openapi.json",
    lifespan=lifespan
)

# Configure OpenAPI documentation
app = configure_openapi(app)

# Add middleware
# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=settings.CORS_ALLOW_CREDENTIALS,
    allow_methods=settings.CORS_METHODS,
    allow_headers=settings.CORS_HEADERS,
)

# Register rate limiter as a Starlette middleware via BaseHTTPMiddleware
app.add_middleware(BaseHTTPMiddleware, dispatch=rate_limiter)
setup_request_validation(app)

# Add GZip compression for responses > 1KB
app.add_middleware(GZipMiddleware, minimum_size=1000)

# Cache control middleware


class CacheControlMiddleware(BaseHTTPMiddleware):
    """Middleware to control caching behavior for different routes"""

    async def dispatch(self, request: Request, call_next):
        response = await call_next(request)

        # Don't cache API responses by default
        if request.url.path.startswith("/api/"):
            response.headers["Cache-Control"] = "no-store, no-cache, must-revalidate"
            response.headers["Pragma"] = "no-cache"
            response.headers["Expires"] = "0"
        # Cache static assets for 1 year
        elif any(ext in request.url.path for ext in [".js", ".css", ".png", ".jpg", ".jpeg", ".gif", ".svg"]):
            response.headers["Cache-Control"] = "public, max-age=31536000"

        return response


app.add_middleware(CacheControlMiddleware)

# Include API routers
app.include_router(api_router, prefix="/api/v1")

# Include admin routes with the /admin prefix
app.include_router(admin_router, tags=["admin"])

# Debug endpoint to list all routes
@app.get("/debug/routes", include_in_schema=False)
async def debug_routes():
    """Debug endpoint to list all registered routes"""
    routes = []
    for route in app.routes:
        route_info = {
            "path": getattr(route, "path", ""),
            "name": getattr(route, "name", ""),
            "methods": list(getattr(route, "methods", [])),
            "endpoint": str(route.endpoint) if hasattr(route, "endpoint") else "",
            "include_in_schema": getattr(route, "include_in_schema", True)
        }
        routes.append(route_info)
    return {"routes": routes}

# Login route
@app.get("/login", response_class=HTMLResponse)
async def login_page(request: Request):
    return templates.TemplateResponse("login.html", {"request": request})

# Login form submission
@app.post("/login")
async def login(
    request: Request,
    username: str = Form(...),
    password: str = Form(...)
):
    # This is a basic implementation - in a real app, use proper authentication
    if username == "admin@example.com" and password == "admin123":
        # Set a session cookie or JWT token here in a real app
        response = RedirectResponse(url="/admin/dashboard", status_code=status.HTTP_303_SEE_OTHER)
        # In a real app, set a secure HTTP-only cookie here
        return response
    return templates.TemplateResponse("login.html", {"request": request, "error": "Invalid username or password", "username": username})

# Debug route to list all routes
@app.get("/debug/routes")
async def debug_routes():
    """List all registered routes for debugging"""
    routes = []
    for route in app.routes:
        route_info = {
            "path": getattr(route, "path", ""),
            "name": getattr(route, "name", ""),
            "methods": list(getattr(route, "methods", [])),
            "type": type(route).__name__
        }
        if hasattr(route, "include_router"):
            route_info["router"] = str(route)
        routes.append(route_info)
    return {"routes": routes}

# Dashboard list endpoint
@app.get("/dashboards", response_class=HTMLResponse)
async def dashboard_list(request: Request):
    """
    Dashboard list page that shows all available dashboards
    """
    try:
        logger.info(f"Templates directory: {TEMPLATES_DIR}")
        logger.info(f"Template exists: {os.path.exists(os.path.join(TEMPLATES_DIR, 'dashboard_list.html'))}")
        
        # Update the dashboard links to point to the correct paths
        dashboards = [
            {
                "title": "Main Dashboard",
                "description": "Overview of all key metrics and activities across the platform.",
                "url": "/dash/",
                "icon": "tachometer-alt",
                "color": "primary"
            },
            {
                "title": "Tax Calculator",
                "description": "Calculate your tax obligations and estimate payments.",
                "url": "/dash/taxcalculate.html",
                "icon": "calculator",
                "color": "success"
            },
            {
                "title": "Business Portal",
                "description": "Manage your business tax information and filings.",
                "url": "/dash/businesses/",
                "icon": "building",
                "color": "info"
            },
            {
                "title": "User Management",
                "description": "Manage user accounts, roles, and permissions.",
                "url": "/dash/Users/",
                "icon": "users-cog",
                "color": "warning"
            }
        ]
        
        # Try to render the template
        template_path = os.path.join(TEMPLATES_DIR, "dashboard_list.html")
        logger.info(f"Attempting to render template at: {template_path}")
        
        if not os.path.exists(template_path):
            logger.error(f"Template not found at: {template_path}")
            raise HTTPException(status_code=500, detail=f"Template not found at: {template_path}")
            
        return templates.TemplateResponse(
            "dashboard_list.html",
            {
                "request": request, 
                "title": "ZRA Dashboards",
                "dashboards": dashboards
            }
        )
    except HTTPException as he:
        logger.error(f"HTTP Error in dashboard_list: {str(he.detail)}")
        raise
    except Exception as e:
        logger.error(f"Unexpected error in dashboard_list: {str(e)}")
        import traceback
        logger.error(traceback.format_exc())
        raise HTTPException(status_code=500, detail=f"Error loading dashboard list: {str(e)}")

# Test route to check template directory
@app.get("/test-templates")
async def test_templates():
    """Test route to check template directory and list files"""
    import os
    
    # Get the directory of the current file
    current_dir = os.path.dirname(os.path.abspath(__file__))
    templates_dir = os.path.join(current_dir, "app", "templates")
    
    # Check if directory exists
    dir_exists = os.path.exists(templates_dir)
    
    # List files if directory exists
    files = []
    if dir_exists:
        try:
            files = os.listdir(templates_dir)
        except Exception as e:
            return {"error": f"Error listing directory: {str(e)}"}
    
    # Try to render a test template
    test_template_path = os.path.join(templates_dir, "test.html")
    template_exists = os.path.exists(test_template_path)
    
    return {
        "current_dir": current_dir,
        "templates_dir": templates_dir,
        "dir_exists": dir_exists,
        "files": files,
        "test_template_exists": template_exists,
        "test_template_path": test_template_path,
        "access_test_url": "/test-template" if template_exists else "Test template not found"
    }

# Test template route
@app.get("/test-template", response_class=HTMLResponse)
async def test_template(request: Request):
    """Test template rendering"""
    from datetime import datetime
    return templates.TemplateResponse(
        "test.html",
        {
            "request": request,
            "now": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
    )

# Root endpoint
@app.get("/", response_class=HTMLResponse)
async def root(request: Request):
    """
    Root endpoint that provides basic application information
    """
    return templates.TemplateResponse(
        "index.html",
        {"request": request, "title": "ZRA Portal", "version": API_VERSION}
    )

# Business Dashboard
@app.get("/business/dashboard", response_class=HTMLResponse)
async def business_dashboard(request: Request):
    """
    Business dashboard endpoint
    """
    # Sample data - replace with actual data from your database
    context = {
        "request": request,
        "business": {
            "name": "Sample Business Ltd",
            "tpin": "1234567890"
        },
        "balance": {
            "outstanding": 15000.00,
            "overdue": 5000.00
        },
        "due_date": {
            "next_due": "2023-12-15",
            "days_remaining": 15
        },
        "stats": {
            "pending_returns": 3,
            "overdue_returns": 1
        },
        "recent_transactions": [
            {
                "date": "2023-10-25",
                "description": "VAT Payment",
                "reference": "TX12345",
                "amount": 5000.00,
                "status": "Paid"
            },
            {
                "date": "2023-10-20",
                "description": "Income Tax",
                "reference": "TX12344",
                "amount": 7500.00,
                "status": "Pending"
            },
            {
                "date": "2023-10-15",
                "description": "VAT Return",
                "reference": "TX12343",
                "amount": 2500.00,
                "status": "Paid"
            }
        ],
        "upcoming_events": [
            {
                "day": "15",
                "month": "NOV",
                "title": "VAT Return Due",
                "description": "Monthly VAT return submission"
            },
            {
                "day": "20",
                "month": "NOV",
                "title": "Income Tax Payment",
                "description": "Quarterly income tax payment"
            }
        ]
    }
    
    return templates.TemplateResponse("business/new_dashboard.html", context)

# Favicon handler
@app.get("/favicon.ico", include_in_schema=False)
async def favicon():
    return Response(status_code=204)  # No content

# Health check endpoint
@app.get("/health", tags=["system"])
async def health_check():
    """
    Health check endpoint

    Returns the status of the application and its dependencies.
    """
    redis_status = "connected" if redis_manager.ping() else "disconnected"

    return {
        "status": "healthy",
        "timestamp": datetime.utcnow().isoformat(),
        "version": API_VERSION,
        "services": {
            "redis": redis_status,
            "database": "connected"  # Add database health check if needed
        }
    }

# Mount static files at root with cache control headers


class SPAStaticFiles(StaticFiles):
    """Static file server with SPA support"""

    async def get_response(self, path: str, scope):
        response = await super().get_response(path, scope)
        # Don't cache HTML files
        if path.endswith(".html") or "." not in path.split("/")[-1]:
            response.headers["Cache-Control"] = "no-store, no-cache, must-revalidate"
            response.headers["Pragma"] = "no-cache"
            response.headers["Expires"] = "0"
        return response


# Mount static files (but not at root yet)
static_files = StaticFiles(directory="static")
app.mount("/static", static_files, name="static")

# Mount the dash directory for dashboard files
try:
    dash_dir_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "dash")
    if os.path.exists(dash_dir_path):
        app.mount("/dash", SPAStaticFiles(directory=dash_dir_path, html=True), name="dash")
    else:
        logger.warning(f"Dash directory not found at {dash_dir_path}")
except Exception as e:
    logger.error(f"Failed to mount /dash: {e}")

# Mount the legacy base dashboards so they are accessible via FastAPI
try:
    base_dir_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "base")
    if os.path.exists(base_dir_path):
        app.mount("/base", SPAStaticFiles(directory=base_dir_path, html=True), name="base")
    else:
        logger.warning(f"Base dashboards directory not found at {base_dir_path}")
except Exception as e:
    logger.error(f"Failed to mount /base: {e}")

@app.get("/dashboards", include_in_schema=False)
async def dashboards_redirect():
    return RedirectResponse(url="/base/dashboards.html")

# Check template existence route
@app.get("/check-template/{template_name}")
async def check_template(template_name: str):
    template_path = os.path.join(TEMPLATES_DIR, template_name)
    exists = os.path.exists(template_path)
    return {
        "template": template_name,
        "exists": exists,
        "path": template_path,
        "files_in_dir": os.listdir(TEMPLATES_DIR) if os.path.exists(TEMPLATES_DIR) else []
    }

# Simple test route
@app.get("/test-template")
async def test_template():
    test_path = os.path.join(TEMPLATES_DIR, "test.html")
    try:
        with open(test_path, 'w') as f:
            f.write("<h1>Test Template</h1><p>If you see this, templates are working!</p>")
        return {"status": "success", "message": f"Test template created at {test_path}"}
    except Exception as e:
        return {"status": "error", "message": f"Failed to create test template: {str(e)}"}

@app.get("/debug/routes")
async def list_routes():
    """List all registered routes"""
    routes = []
    for route in app.routes:
        routes.append({
            "path": route.path,
            "name": getattr(route, "name", ""),
            "methods": list(route.methods) if hasattr(route, "methods") else []
        })
    return {"routes": routes}


# Include the dashboard router in the main app
# Note: The router already has a prefix of "/dash"
app.include_router(dash_router)

# Mount assets folder for CSS/JS files
assets_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "dashboard", "assets")
if os.path.exists(assets_dir):
    logger.info(f"Mounting /assets from {assets_dir}")
    app.mount(
        "/assets",
        StaticFiles(directory=assets_dir),
        name="assets"
    )
else:
    logger.warning(f"Assets directory not found at {assets_dir}")

# Mount dashboard files (but not at root yet)
dashboard_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "dashboard")
if os.path.exists(dashboard_dir):
    logger.info(f"Mounting dashboard files from {dashboard_dir}")
    dashboard_files = SPAStaticFiles(directory=dashboard_dir, html=True)
    # Mount to a specific path instead of root
    app.mount("/dash", dashboard_files, name="dashboard")
else:
    logger.warning(f"Dashboard directory not found at {dashboard_dir}")

if __name__ == "__main__":
    # Enable debug mode for detailed error messages
    import os
    os.environ["DEBUG"] = "True"
    
    # Start the server with debug settings
    uvicorn.run(
        "main:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=False,  # Keep reload disabled to avoid path issues
        ssl_keyfile=settings.SSL_KEYFILE if settings.SSL_KEYFILE else None,
        ssl_certfile=settings.SSL_CERTFILE if settings.SSL_CERTFILE else None,
        log_level="debug",  # Set to debug for more detailed logs
        proxy_headers=True,
        forwarded_allow_ips='*'
    )
