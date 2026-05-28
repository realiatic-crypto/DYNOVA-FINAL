"""
FastAPI reverse proxy: forwards all /api/* requests to the local PHP server.

This lets us run the DYNOVA NETWORK PHP app on the Emergent preview without
modifying the Kubernetes ingress (which routes /api -> port 8001).
"""
import os
import httpx
from fastapi import FastAPI, Request, Response
from fastapi.responses import PlainTextResponse

PHP_UPSTREAM = os.environ.get("DYNOVA_PHP_UPSTREAM", "http://127.0.0.1:9000")

app = FastAPI(title="Dynova PHP Proxy")

_client: httpx.AsyncClient | None = None


@app.on_event("startup")
async def _startup() -> None:
    global _client
    _client = httpx.AsyncClient(
        base_url=PHP_UPSTREAM,
        follow_redirects=False,
        timeout=httpx.Timeout(30.0, connect=5.0),
    )


@app.on_event("shutdown")
async def _shutdown() -> None:
    if _client:
        await _client.aclose()


# Hop-by-hop headers that shouldn't be forwarded
_HOP_HEADERS = {
    "connection", "keep-alive", "proxy-authenticate", "proxy-authorization",
    "te", "trailers", "transfer-encoding", "upgrade", "host",
    "content-length", "content-encoding",
}


@app.get("/api/health")
async def health() -> dict:
    return {"ok": True, "upstream": PHP_UPSTREAM}


async def _proxy(request: Request, path: str) -> Response:
    assert _client is not None
    # Build upstream URL – strip the "/api" prefix so PHP receives clean paths
    upstream_path = "/" + path
    query = request.url.query
    if query:
        upstream_path = f"{upstream_path}?{query}"

    body = await request.body()
    original_host = request.headers.get("host", "")
    headers = {
        k: v for k, v in request.headers.items()
        if k.lower() not in _HOP_HEADERS
    }
    # PHP's built-in server doesn't handle keep-alive properly; force close
    headers["Connection"] = "close"
    # Tell PHP what the original public host & prefix were so it can build
    # correct absolute URLs (e.g. referral links).
    if original_host:
        headers["X-Forwarded-Host"] = original_host
    headers["X-Forwarded-Prefix"] = "/api"
    headers["X-Forwarded-Proto"] = request.url.scheme

    try:
        upstream = await _client.request(
            request.method, upstream_path,
            content=body, headers=headers,
        )
    except httpx.ConnectError:
        return PlainTextResponse(
            "PHP upstream unavailable. Please wait for it to start.", status_code=502
        )

    # Preserve multi-value headers (e.g. multiple Set-Cookie) by writing raw_headers
    resp = Response(content=upstream.content, status_code=upstream.status_code)
    raw = []
    for k, v in upstream.headers.multi_items():
        if k.lower() in _HOP_HEADERS:
            continue
        raw.append((k.lower().encode("latin-1"), v.encode("latin-1")))
    resp.raw_headers = raw
    return resp


@app.api_route("/api", methods=["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"])
async def proxy_root(request: Request) -> Response:
    return await _proxy(request, "")


@app.api_route("/api/", methods=["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"])
async def proxy_root_slash(request: Request) -> Response:
    return await _proxy(request, "")


@app.api_route("/api/{path:path}", methods=["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"])
async def proxy_any(request: Request, path: str) -> Response:
    return await _proxy(request, path)
