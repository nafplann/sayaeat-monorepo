# Environment Setup Guide

## Overview

This guide explains the environment configuration for the BFF architecture, including Pyramid (data service) and Merchant BFF.

## Architecture

```
┌─────────────────┐         ┌─────────────────┐
│  Merchant BFF   │ ◄─────► │    Pyramid      │
│  Port: 8001     │  API    │  Port: 8000     │
│  Stateless      │         │  MySQL          │
│  (no database)  │         │  (all data)     │
└─────────────────┘         └─────────────────┘
```

## Services

### Pyramid (Data Service)
- **Container**: `dev-pyramid-app`
- **Port**: 8000 (host) → 8000 (container)
- **Database**: MySQL 8
  - Host: `dev-pyramid-mysql`
  - Port: 3306
  - Database: `db_wa`
  - User: `db_wa`
  - Password: `123qweASD`

### Merchant BFF
- **Container**: `merchant-bff`
- **Port**: 8001 (host) → 8000 (container)
- **Database**: None! (Stateless BFF)
  - Sessions: Cookie-based (encrypted)
  - Cache: File-based
  - All data: Fetched from Pyramid via API

## Environment Files

### 1. Pyramid (.env.local)

```bash
APP_NAME="WA Aja"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://dev.merchant.wa-aja.com
APP_KEY=base64:8JF7LwnNmEKqGvMSBslZaAarzDuUWKoXI3BqsvSfH6c=

DB_CONNECTION=mysql
DB_HOST=dev-pyramid-mysql
DB_PORT=3306
DB_DATABASE=db_wa
DB_USERNAME=db_wa
DB_PASSWORD=123qweASD

SESSION_DRIVER=database

# Firebase, Mapbox, Sentry, ReCAPTCHA configs...
# (existing configs remain)

# Internal API Keys for BFF Services
MERCHANT_BFF_API_KEY=merchant-bff-dev-key-5k9mz2x7p4qw8n3
HAPI_BFF_API_KEY=hapi-bff-dev-key-7p2k5x9m3w8q4n6
HORUS_BFF_API_KEY=horus-bff-dev-key-3x8k2p9m5w7q4n1
```

### 2. Merchant BFF (.env)

```bash
APP_NAME="WA Aja - Merchant Portal"
APP_ENV=local
APP_KEY=base64:BHrC5I6sBmHlk/9QJSeC3CXGITWSnnLZv/BmNWm7r3Q=
APP_DEBUG=true
APP_URL=http://localhost:8001

# No database needed - BFF is stateless!
# All business data comes from Pyramid via API

# Session & Cache (No Database Required)
SESSION_DRIVER=cookie
SESSION_ENCRYPT=true
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Pyramid Data Service Configuration
PYRAMID_API_URL=http://app:8000/api
PYRAMID_API_KEY=merchant-bff-dev-key-5k9mz2x7p4qw8n3
PYRAMID_TIMEOUT=30
PYRAMID_CACHE_TTL=600
PYRAMID_RETRY_TIMES=3
PYRAMID_RETRY_SLEEP=100
```

## Docker Compose Configuration

The root `docker-compose.dev.yaml` orchestrates all services:

```yaml
services:
  mysql:
    # Pyramid's MySQL database
    
  app:
    # Pyramid application
    depends_on: [mysql]
    
  merchant-bff:
    # Merchant BFF application (stateless, no database)
    depends_on: [app]
```

## API Key Authentication

### How It Works

1. **Merchant BFF** sends requests to Pyramid with the API key in the header:
   ```
   X-API-Key: merchant-bff-dev-key-5k9mz2x7p4qw8n3
   ```

2. **Pyramid** validates the API key using the `ApiKeyMiddleware`:
   - Checks against `config/services.php` → `internal_api_keys` array
   - Keys are loaded from environment variables:
     - `MERCHANT_BFF_API_KEY`
     - `HAPI_BFF_API_KEY`
     - `HORUS_BFF_API_KEY`

3. **PyramidClient** (in shared package) automatically adds the API key to all requests

### Security Notes

- **Development keys** are simple strings for local testing
- **Production keys** should be:
  - At least 32 characters
  - Randomly generated (use `openssl rand -hex 32`)
  - Stored in secure environment variables
  - Rotated regularly

## Network Communication

### Inter-Service Communication (Docker)

Services communicate using Docker service names:

```php
// Merchant BFF → Pyramid
PYRAMID_API_URL=http://app:8000/api
```

- `app` is the Docker service name for Pyramid
- Requests stay within the Docker network (faster, more secure)

### External Access (Browser → Services)

- **Pyramid**: http://localhost:8000
- **Merchant BFF**: http://localhost:8001

## Database Setup

### Pyramid (MySQL)

The MySQL database is automatically initialized with a dump:
```
./services/pyramid/database/dump/backup-latest.sql
```

No additional setup needed.

### Merchant BFF (No Database!)

**Merchant BFF is stateless and doesn't need a database!**

- **Sessions**: Stored in encrypted cookies (client-side)
- **Cache**: Stored in files (`storage/framework/cache/`)
- **Jobs**: Run synchronously (no queue)
- **All Data**: Fetched from Pyramid via API

No migrations needed!

## Quick Start

1. **Start all services**:
   ```bash
   cd /Users/abdul.manaf/Documents/appdev/sayaeat-monorepo
   docker compose -f docker-compose.dev.yaml up -d
   ```

2. **Verify services**:
   - Pyramid: http://localhost:8000
   - Merchant BFF: http://localhost:8001

3. **Check logs**:
   ```bash
   # Pyramid logs
   docker logs dev-pyramid-app -f
   
   # Merchant BFF logs
   docker logs merchant-bff -f
   ```

## Troubleshooting

### Issue: Merchant BFF can't connect to Pyramid

**Check**:
1. Pyramid is running: `docker ps | grep pyramid`
2. API key is correct in both `.env` files
3. Network connectivity: `docker exec merchant-bff ping app`

**Solution**:
```bash
# Restart services
docker compose -f docker-compose.dev.yaml restart app merchant-bff
```

### Issue: Merchant BFF crashes or has errors

**Check**:
1. Pyramid is running and accessible
2. Session/cache directories are writable: `storage/framework/{sessions,cache,views}`

**Solution**:
```bash
# Ensure storage directories exist and are writable
docker exec merchant-bff mkdir -p storage/framework/{sessions,cache,views}
docker exec merchant-bff chmod -R 775 storage

# Restart Merchant BFF
docker compose -f docker-compose.dev.yaml restart merchant-bff
```

### Issue: API key validation failed

**Check**:
1. `PYRAMID_API_KEY` in Merchant BFF `.env`
2. `MERCHANT_BFF_API_KEY` in Pyramid `.env.local`
3. Both keys match exactly

**Debug**:
```bash
# Check Pyramid's API key config
docker exec dev-pyramid-app php artisan tinker
>>> config('services.internal_api_keys');

# Check Merchant BFF's Pyramid client config
docker exec merchant-bff php artisan tinker
>>> config('pyramid');
```

## Production Considerations

### Environment Variables

Update these for production:

1. **Generate new API keys**:
   ```bash
   # Generate secure API keys
   openssl rand -hex 32  # For MERCHANT_BFF_API_KEY
   openssl rand -hex 32  # For HAPI_BFF_API_KEY
   openssl rand -hex 32  # For HORUS_BFF_API_KEY
   ```

2. **Update APP_KEY**:
   ```bash
   php artisan key:generate
   ```

3. **Set APP_ENV and APP_DEBUG**:
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **Configure production URLs**:
   ```bash
   # Pyramid
   APP_URL=https://api.wa-aja.com
   
   # Merchant BFF
   APP_URL=https://merchant.wa-aja.com
   PYRAMID_API_URL=https://api.wa-aja.com/api
   ```

### Database

**Pyramid:**
- Use managed database services (RDS, Cloud SQL)
- Enable SSL connections
- Regular backups and replication
- Proper connection pooling

**Merchant BFF:**
- No database needed!
- Consider Redis for production caching (optional)
- Ensure `storage/` directory is persistent and backed up

### Security

- Use HTTPS for all services
- Implement rate limiting
- Enable CORS restrictions
- Monitor API key usage
- Rotate keys regularly
- Use secrets management (AWS Secrets Manager, HashiCorp Vault)

## Next Steps

After environment setup:

1. ✅ **Test Pyramid Internal API** - Use Postman/Insomnia
2. ✅ **Test Merchant BFF authentication** - Login/logout flow
3. ✅ **Test merchant CRUD** - Create, read, update, delete merchants
4. ✅ **Test menu management** - Menus, categories, addons
5. ✅ **Test order management** - View and process orders
6. ✅ **Deploy to staging** - Test in staging environment
7. ✅ **QA testing** - Full regression testing
8. ✅ **Deploy to production** - Final deployment

## Support

For issues or questions:
- Check logs: `docker logs <container-name>`
- Review API documentation: `INTERNAL_API.md`
- Check migration plan: `BFF_MIGRATION_PLAN.md`

