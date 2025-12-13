# Environment Configuration Changes Summary

## Overview
Configured environment for a **stateless BFF architecture** - Merchant BFF requires NO database!

## Key Architecture Decision

### ✨ Merchant BFF is Stateless!

**No PostgreSQL needed!** The Merchant BFF uses:
- **Sessions**: Cookie-based (encrypted, client-side)
- **Cache**: File-based (`storage/framework/cache/`)
- **Jobs**: Synchronous (no queue)
- **All Data**: Fetched from Pyramid via API

This simplifies deployment, reduces infrastructure costs, and follows true BFF principles.

## Files Modified

### 1. docker-compose.dev.yaml
**Removed PostgreSQL** - Not needed!
- Merchant BFF depends only on Pyramid (`app`)
- No database service required
- No volumes for database storage

### 2. services/pyramid/.env.example
**Added Internal API Keys:**
```bash
MERCHANT_BFF_API_KEY=merchant-bff-secret-key-change-in-production
HAPI_BFF_API_KEY=hapi-bff-secret-key-change-in-production
HORUS_BFF_API_KEY=horus-bff-secret-key-change-in-production
```

### 3. services/merchant-bff/.env.example
**Updated for Stateless Architecture:**
- `APP_NAME="WA Aja - Merchant Portal"`
- `APP_URL=http://localhost:8001`
- `SESSION_DRIVER=cookie` (encrypted cookies, no database)
- `CACHE_STORE=file` (file-based cache, no database)
- `QUEUE_CONNECTION=sync` (synchronous, no database)
- **Removed all database configuration** (no DB_HOST, DB_PORT, etc.)

**Added Pyramid Configuration:**
```bash
PYRAMID_API_URL=http://app:8000/api
PYRAMID_API_KEY=merchant-bff-secret-key-change-in-production
PYRAMID_TIMEOUT=30
PYRAMID_CACHE_TTL=600
PYRAMID_RETRY_TIMES=3
PYRAMID_RETRY_SLEEP=100
```

### 4. services/pyramid/.env.local (gitignored)
**Added Internal API Keys:**
```bash
MERCHANT_BFF_API_KEY=merchant-bff-dev-key-5k9mz2x7p4qw8n3
HAPI_BFF_API_KEY=hapi-bff-dev-key-7p2k5x9m3w8q4n6
HORUS_BFF_API_KEY=horus-bff-dev-key-3x8k2p9m5w7q4n1
```

### 5. services/merchant-bff/.env (gitignored)
**Configured for Cookie Sessions and File Cache:**
- Session driver: `cookie`
- Cache store: `file`
- Queue: `sync`
- Pyramid API URL: `http://app:8000/api`
- API Key: Matching Pyramid's configuration

## New Documentation

### ENV_SETUP_GUIDE.md
Comprehensive environment setup guide covering:
- Stateless BFF architecture
- Service configuration
- API key authentication
- Network communication
- Quick start guide
- Troubleshooting
- Production considerations

## Architecture Benefits

### Why Stateless BFF is Better

**Simplicity:**
- No database migrations to manage
- No database connection pooling issues
- Fewer moving parts = fewer failure points

**Scalability:**
- Horizontally scale BFF instances instantly
- No database replication needed
- No session synchronization issues

**Cost:**
- One less database to manage and pay for
- Reduced infrastructure complexity
- Lower operational overhead

**Security:**
- Encrypted cookie sessions (built-in Laravel security)
- No session data in database to leak
- Single source of truth (Pyramid) for all business data

**Performance:**
- File cache is fast for small datasets
- No database round-trips for sessions
- Can add Redis later if needed

## API Key Configuration

### Development Keys (Local)
- Simple strings for easy testing
- Keys match between Pyramid and Merchant BFF
- MERCHANT_BFF_API_KEY: `merchant-bff-dev-key-5k9mz2x7p4qw8n3`

### How It Works
1. Merchant BFF sends API key in header: `X-API-Key: merchant-bff-dev-key-5k9mz2x7p4qw8n3`
2. Pyramid validates against `config/services.php` → `internal_api_keys` array
3. PyramidClient (shared package) automatically includes the key

### Production Keys (Future)
- Use `openssl rand -hex 32` to generate secure keys
- Store in secure environment variables or secrets management
- Rotate regularly

## Docker Services

### Current Setup
```yaml
services:
  mysql:          # Pyramid's database
  app:            # Pyramid application
  merchant-bff:   # Merchant BFF (stateless, no database!)
```

### What's Running
- **MySQL (port 3306)**: Pyramid's database with all business data
- **Pyramid (port 8000)**: Data service exposing internal APIs
- **Merchant BFF (port 8001)**: Stateless frontend service

## Network Configuration

### Inter-Service Communication (Docker Network)
- Merchant BFF → Pyramid: `http://app:8000/api`
- Uses Docker service names (internal network)

### External Access (Browser)
- Pyramid: `http://localhost:8000`
- Merchant BFF: `http://localhost:8001`

## Session Management

### How Cookie Sessions Work

1. **User logs in** via Merchant BFF
2. **Merchant BFF** validates credentials with Pyramid API
3. **Session data** stored in encrypted cookie
4. **Cookie sent** with every request (automatic)
5. **No database** needed for session storage

### Security
- Cookies are encrypted with `APP_KEY`
- HttpOnly flag prevents JavaScript access
- Secure flag in production (HTTPS only)
- SameSite protection against CSRF

## Cache Strategy

### File-Based Cache
- Fast for moderate traffic
- No external dependencies
- Automatic cleanup of old cache files
- Located in `storage/framework/cache/`

### Future: Redis Cache (Optional)
If traffic grows, easily switch to Redis:
```bash
CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

## Quick Start

```bash
# 1. Start services
docker compose -f docker-compose.dev.yaml up -d

# 2. Verify services are running
docker ps

# 3. Test Merchant BFF
curl http://localhost:8001

# 4. Check logs if needed
docker logs merchant-bff -f
```

That's it! No database migrations, no complex setup.

## Production Considerations

### Scaling
- Scale Merchant BFF horizontally (multiple instances)
- Load balancer distributes traffic
- Each instance is independent (stateless)
- No session synchronization needed

### Persistent Storage
- Ensure `storage/` directory is persistent
- Use shared storage (NFS, S3) for multi-instance deployments
- Or use Redis for cache in production

### Monitoring
- Monitor API latency (BFF → Pyramid)
- Track cache hit rates
- Alert on API key failures
- Monitor cookie session issues

## Files to Commit

```
modified:   docker-compose.dev.yaml
modified:   services/pyramid/.env.example
modified:   services/merchant-bff/.env.example
new file:   ENV_SETUP_GUIDE.md
new file:   ENV_CHANGES_SUMMARY.md
```

## What Changed from Initial Plan

**Initial Plan:**
- Merchant BFF with PostgreSQL database
- Database for users, sessions, cache, jobs

**Final Implementation:**
- Merchant BFF is stateless (no database!)
- Cookie-based sessions
- File-based cache
- Synchronous jobs

**Why the Change:**
- BFF should be stateless by design
- Simpler architecture
- Easier to scale
- Lower operational overhead
- True separation of concerns

## Next Steps

1. ✅ **Review and approve changes**
2. ✅ **Commit configuration** with `[ai:n]`
3. ✅ **Start services** and test
4. ✅ **Test authentication flow**
5. ✅ **Test merchant CRUD operations**

---

**Note**: .env files are gitignored and not committed. Only .env.example files are version controlled.
