# Architecture Decision Record: BFF Pattern Migration

**Status:** Proposed  
**Date:** October 11, 2025  
**Deciders:** Platform Team  

---

## Context

SayaEat currently has a Laravel monolith (Pyramid) serving three different clients:
1. **Merchant Portal** - Web-based admin interface for merchants
2. **Hapi** - Mobile app for customers  
3. **Horus** - Mobile app for drivers

Each client has different requirements, response formats, and business logic. The monolith is becoming difficult to maintain and scale.

---

## Decision

We will migrate to a **Backend for Frontend (BFF) pattern** with the following architecture:

### Services
1. **Merchant BFF** - Dedicated service for merchant portal
2. **Hapi BFF** - Dedicated service for customer mobile app
3. **Pyramid Data Service** - Core data layer with business logic
4. **Shared Package** - Common code (Enums, DTOs, Utils, Client)

### Key Architectural Decisions

#### 1. Authentication Strategy: **Token-Based Gateway Pattern**

**Decision:** BFFs validate authentication tokens via Pyramid's internal API

**Rationale:**
- Pyramid remains single source of truth for authentication
- No user data duplication
- Consistent security model
- Simpler to maintain and audit

**Implementation:**
```
Client → BFF (validates token with Pyramid) → Pyramid (authoritative)
```

**Trade-offs:**
- ✅ Single auth source (no sync issues)
- ✅ Consistent security
- ✅ Easy rollback
- ❌ Extra latency (mitigated by caching)
- ❌ Network dependency (mitigated by retry logic)

#### 2. Data Access: **API Gateway Pattern**

**Decision:** BFFs access data only through Pyramid's internal REST APIs

**Rationale:**
- Clear separation of concerns
- Pyramid maintains data consistency
- Easier to add business rules in one place
- No database coupling between services

**Alternatives Considered:**
- ❌ **Direct database access**: Too much coupling, duplicate logic
- ❌ **Shared database with ORMs**: Race conditions, migration complexity
- ❌ **Event sourcing**: Overcomplicated for current needs

#### 3. Shared Code: **Composer Package**

**Decision:** Create `sayaeat/shared` package for common code

**What goes in shared package:**
- ✅ Enums (OrderStatus, ServiceEnum, etc.)
- ✅ DTOs (Data Transfer Objects)
- ✅ Utils (DistanceCalculator, FeeCalculator)
- ✅ Contracts (Interfaces)
- ✅ Pyramid HTTP Client
- ❌ Models (stay in Pyramid)
- ❌ Database migrations (stay in Pyramid)

**Rationale:**
- Reusable code across services
- Single source of truth for business constants
- Type safety across services
- Easy to version and update

#### 4. Communication: **Synchronous REST APIs**

**Decision:** Use REST APIs with API key authentication for inter-service communication

**Rationale:**
- Simple and well-understood
- Good for request-response patterns
- Easy to debug and monitor
- Laravel ecosystem support

**Alternatives Considered:**
- ❌ **GraphQL**: Overkill for internal APIs
- ❌ **gRPC**: PHP support not mature
- ⏳ **Message queues**: Consider for async operations later

#### 5. Caching Strategy: **Redis with TTL**

**Decision:** Cache auth validations and frequently accessed data in Redis

**Cache Keys:**
- `token:{token_hash}` - Sanctum token validation (TTL: 10 min)
- `user:{user_id}` - User data (TTL: 10 min)
- `merchant:{merchant_id}` - Merchant data (TTL: 5 min)
- `menu:{merchant_id}` - Menu data (TTL: 5 min)

**Invalidation:**
- TTL-based expiration
- Manual invalidation on updates
- Future: Webhook-based invalidation from Pyramid

#### 6. Migration Order: **Merchant → Hapi → Driver**

**Decision:** Migrate Merchant BFF first, then Hapi BFF, keep Driver in Pyramid temporarily

**Rationale:**
- **Merchant BFF first:**
  - Simpler authentication (session-based)
  - Clearer bounded context
  - Less real-time complexity
  - Smaller API surface
  - Lower risk (fewer users)

- **Hapi BFF second:**
  - More complex (token-based auth)
  - Larger API surface
  - Real-time requirements
  - Higher traffic

- **Driver stays:**
  - Under spec review
  - Least urgent
  - Simplest to keep in Pyramid

#### 7. Deployment: **Gradual Traffic Migration**

**Decision:** Deploy BFFs alongside Pyramid, gradually shift traffic with feature flags

**Phases:**
1. Deploy BFF (0% traffic)
2. Test BFF in production
3. Route 10% traffic to BFF
4. Monitor errors and latency
5. Gradually increase to 100%
6. Remove old code from Pyramid

**Rollback:**
- Feature flag to route 100% back to Pyramid
- No data loss (Pyramid still owns data)
- Fast rollback (< 1 minute)

---

## Consequences

### Positive

✅ **Better separation of concerns**
- Each BFF optimized for its client
- Clear service boundaries
- Easier to understand and maintain

✅ **Independent scaling**
- Scale Merchant BFF and Hapi BFF independently
- Different resource requirements

✅ **Team autonomy**
- Teams can work on BFFs independently
- Faster development cycles

✅ **Better performance**
- Tailored responses for each client
- No over-fetching
- Client-specific caching

✅ **Easier testing**
- Test each BFF in isolation
- Mock Pyramid responses

✅ **Better security**
- API keys for internal communication
- Rate limiting per service
- Isolated blast radius

### Negative

❌ **Increased complexity**
- More services to manage
- More deployment pipelines
- Inter-service communication overhead

❌ **Network latency**
- Additional hop (BFF → Pyramid)
- Mitigated by caching

❌ **Data consistency challenges**
- Cache invalidation complexity
- Eventual consistency in caches

❌ **Code duplication**
- Some business logic may duplicate
- Need to keep shared package in sync

---

## Security Considerations

### API Key Management
- API keys stored in environment variables
- Different keys for each BFF
- Rotate keys regularly
- Use secrets management (AWS Secrets Manager, Vault)

### Token Validation
- All tokens validated with Pyramid
- Cache validation results with short TTL
- Invalidate cache on logout

### Network Security
- Internal APIs not exposed publicly
- Use private network/VPC
- TLS for all inter-service communication
- Firewall rules: Only BFFs can access Pyramid internal APIs

---

## Performance Considerations

### Latency Budget
- Target: < 100ms added latency from BFF layer
- Auth validation: < 50ms (with cache)
- Data fetching: < 50ms (with cache)

### Caching Strategy
- Redis cache for auth validation
- Redis cache for frequently accessed data
- Cache hit rate target: > 90%

### Connection Pooling
- HTTP/2 persistent connections
- Connection pool size: 100
- Keep-alive: 60 seconds

### Monitoring
- Request latency (p50, p95, p99)
- Error rates
- Cache hit rates
- Pyramid API response times

---

## Testing Strategy

### Unit Tests
- Test services in isolation
- Mock Pyramid client
- Test DTOs and transformations

### Integration Tests
- Test BFF → Pyramid communication
- Test authentication flows
- Test error handling

### End-to-End Tests
- Test full user journeys
- Test from client → BFF → Pyramid → Database

### Load Tests
- 1000 req/s per BFF
- Measure latency under load
- Test cache effectiveness

---

## Monitoring & Observability

### Metrics
- Request count per endpoint
- Response time (p50, p95, p99)
- Error rate (4xx, 5xx)
- Cache hit/miss rate
- Pyramid API latency

### Logging
- Structured JSON logs
- Request ID across services
- Log levels: INFO, WARN, ERROR
- Include user context (anonymized)

### Tracing
- Distributed tracing (OpenTelemetry)
- Trace requests across BFF → Pyramid
- Identify slow queries

### Alerting
- Error rate > 5%
- Response time p95 > 500ms
- Pyramid API errors
- Cache failures

---

## Open Questions

1. **Should BFFs have read replicas for performance?**
   - Consideration: Reduce load on Pyramid
   - Risk: Eventual consistency issues

2. **Should we use message queues for async operations?**
   - Use case: Notifications, reports, long-running tasks
   - Tool: RabbitMQ, Redis Queue, AWS SQS

3. **Should we add a GraphQL layer?**
   - Benefit: Flexible queries for mobile apps
   - Cost: Additional complexity

4. **Should we implement service mesh?**
   - Tool: Istio, Linkerd
   - Benefit: Better traffic management, security
   - Cost: Operational complexity

---

## Related Documents

- [BFF_MIGRATION_PLAN.md](./BFF_MIGRATION_PLAN.md) - Detailed migration plan
- [BFF_QUICK_START.md](./BFF_QUICK_START.md) - Implementation guide
- API documentation (to be created)

---

## References

- [BFF Pattern - Sam Newman](https://samnewman.io/patterns/architectural/bff/)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Microservices Patterns - Chris Richardson](https://microservices.io/patterns/index.html)

---

**Last Updated:** October 11, 2025

