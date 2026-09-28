from contextlib import asynccontextmanager
from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from .core.config import get_settings
from .db import open_pool, close_pool
from .api import catalogue, orders, enquiries, visual_search, auth, admin, storefront

settings = get_settings()

@asynccontextmanager
async def lifespan(_app: FastAPI):
    open_pool()
    yield
    close_pool()

app = FastAPI(title=settings.app_name, version="2.0.0", docs_url="/docs" if settings.app_env != "production" else None)
app.add_middleware(CORSMiddleware, allow_origins=settings.cors_origin_list, allow_credentials=True, allow_methods=["*"], allow_headers=["*"])

@app.middleware("http")
async def security_headers(request: Request, call_next):
    response = await call_next(request)
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "SAMEORIGIN"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    response.headers["Permissions-Policy"] = "camera=(self), microphone=()"
    return response

@app.exception_handler(Exception)
async def unhandled_exception(_request: Request, exc: Exception):
    if settings.app_debug:
        return JSONResponse(status_code=500, content={"success": False, "error": str(exc)})
    return JSONResponse(status_code=500, content={"success": False, "error": "Internal server error."})

@app.get("/health")
def health():
    return {"status":"ok","service":settings.app_name,"version":"2.0.0"}

for router in (catalogue.router, orders.router, enquiries.router, visual_search.router, auth.router, admin.router, storefront.router):
    app.include_router(router)
