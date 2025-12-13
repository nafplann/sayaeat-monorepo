# BFF Migration - Executive Summary

**Date:** October 11, 2025  
**Status:** Planning Complete - Ready for Implementation

---

## 🎯 Objective

Migrate SayaEat's Pyramid monolith to a **Backend for Frontend (BFF)** pattern to improve:
- Code maintainability
- Team autonomy
- Service scalability
- Client-specific optimization

---

## 📊 Current State

```
┌─────────────────────────────────────┐
│         Pyramid Monolith            │
│  ┌───────────────────────────────┐  │
│  │ Merchant Portal (web.php)     │  │
│  │ User App (api.php /v1)        │  │
│  │ Driver App (api.php /v1/horus)│  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

---

## 🎯 Target State

```
┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│ Merchant BFF │   │  Hapi BFF    │   │   Pyramid    │
│ (Portal)     │   │ (User App)   │   │ (Data Layer  │
│              │   │              │   │  + Driver)   │
└──────┬───────┘   └──────┬───────┘   └──────┬───────┘
       │                  │                   │
       └──────────────────┴───────────────────┘
                          │
                   ┌──────┴──────┐
                   │   Shared    │
                   │   Package   │
                   └─────────────┘
```

---

## 🔑 Key Decisions

### 0. Authentication Strategy

**Answer: Token-Based Gateway Pattern**

- ✅ Pyramid remains **single source of truth** for authentication
- ✅ BFFs validate tokens via Pyramid's internal API
- ✅ Cache validation results in Redis (10-min TTL)
- ✅ No user data duplication

**Flow:**
```
Client → BFF (validate token) → Pyramid → Response
                ↓
              Cache (Redis)
```

### 1. Migration Order

**Answer: Merchant → Hapi → Driver**

1. **Merchant BFF (First)** ✅
   - Simpler (session-based auth)
   - Lower risk (fewer users)
   - Clear bounded context
   
2. **Hapi BFF (Second)** ⏳
   - More complex (token-based auth)
   - Higher traffic
   - Multiple services (MakanAja, MarketAja, KirimAja)
   
3. **Driver in Pyramid (Temporary)** ⏸️
   - Under spec review
   - Will migrate to Horus BFF later

### 2. Shared Package

**Answer: Create `packages/sayaeat-shared`**

**Includes:**
- ✅ Enums (OrderStatus, ServiceEnum, etc.)
- ✅ DTOs (Data Transfer Objects)
- ✅ Utils (DistanceCalculator, FeeCalculator)
- ✅ Pyramid HTTP Client
- ✅ Contracts (Interfaces)

**Excludes:**
- ❌ Models (stay in Pyramid)
- ❌ Database migrations (stay in Pyramid)

### 3. Pyramid as Data Service

**Answer: Transform Pyramid to internal API service**

**New Routes:**
- `POST /api/internal/auth/validate-token`
- `POST /api/internal/auth/validate-session`
- `GET /api/internal/merchants`
- `POST /api/internal/orders`
- ... (complete CRUD for all resources)

**Security:**
- API key authentication (`X-Api-Key` header)
- Different keys for each BFF
- Not exposed publicly

### 4. Communication Pattern

**Answer: Synchronous REST APIs**

- HTTP/REST for BFF ↔ Pyramid
- API key authentication
- Redis caching (10-min TTL)
- Retry logic (3 attempts)

---

## 📅 Timeline

| Phase | Duration | Tasks |
|-------|----------|-------|
| **Phase 1: Foundation** | 3-5 days | Create shared package |
| **Phase 2: Pyramid APIs** | 5-7 days | Build internal APIs |
| **Phase 3: Merchant BFF** | 10-14 days | Migrate merchant portal |
| **Phase 4: Hapi BFF** | 10-14 days | Migrate user app |
| **Phase 5: Cleanup** | 3-5 days | Remove old code |
| **Total** | **31-45 days** | |

---

## ✅ Implementation Checklist

### Week 1-2: Foundation & Pyramid APIs
- [ ] Create `/packages/sayaeat-shared`
- [ ] Copy Enums & Utils to shared package
- [ ] Create DTOs and contracts
- [ ] Create PyramidClient base class
- [ ] Create internal API routes in Pyramid
- [ ] Create API key middleware
- [ ] Build internal controllers (Auth, Merchants, Orders, etc.)
- [ ] Test internal APIs

### Week 3-4: Merchant BFF
- [ ] Install shared package in Merchant BFF
- [ ] Configure Pyramid client
- [ ] Copy routes from Pyramid
- [ ] Copy controllers and refactor to use PyramidService
- [ ] Copy views and assets
- [ ] Implement session-based auth with Pyramid
- [ ] Test all merchant portal features
- [ ] Deploy to staging
- [ ] QA testing
- [ ] Deploy to production (gradual rollout)

### Week 5-6: Hapi BFF
- [ ] Install shared package in Hapi BFF
- [ ] Configure Pyramid client
- [ ] Copy API routes from Pyramid
- [ ] Copy controllers and refactor
- [ ] Implement token validation middleware
- [ ] Setup Redis caching
- [ ] Test all mobile app features
- [ ] Deploy to staging
- [ ] Mobile app testing
- [ ] Deploy to production (gradual rollout)

### Week 7: Cleanup
- [ ] Remove migrated code from Pyramid
- [ ] Performance optimization
- [ ] Documentation updates
- [ ] Monitoring setup

---

## 📁 File Structure

```
sayaeat-monorepo/
├── packages/
│   └── sayaeat-shared/
│       ├── composer.json
│       └── src/
│           ├── DTOs/
│           ├── Enums/
│           ├── Utils/
│           ├── Contracts/
│           └── Clients/
│               └── PyramidClient.php
├── services/
│   ├── merchant-bff/
│   │   ├── app/
│   │   │   ├── Http/Controllers/
│   │   │   ├── Services/
│   │   │   │   ├── PyramidService.php
│   │   │   │   └── AuthService.php
│   │   │   └── Providers/
│   │   ├── config/
│   │   │   └── pyramid.php
│   │   └── routes/
│   │       └── web.php
│   ├── hapi-bff/
│   │   ├── app/
│   │   │   ├── Http/Controllers/Api/
│   │   │   ├── Services/
│   │   │   └── Middleware/
│   │   ├── config/
│   │   │   └── pyramid.php
│   │   └── routes/
│   │       └── api.php
│   └── pyramid/
│       ├── app/
│       │   ├── Http/Controllers/Internal/
│       │   │   ├── AuthController.php
│       │   │   ├── MerchantsController.php
│       │   │   └── OrdersController.php
│       │   ├── Middleware/
│       │   │   └── ApiKeyMiddleware.php
│       │   └── Models/
│       ├── config/
│       │   └── services.php
│       └── routes/
│           ├── api.php (driver routes only)
│           └── internal-api.php (NEW)
└── docs/
    ├── BFF_MIGRATION_PLAN.md
    ├── BFF_QUICK_START.md
    ├── BFF_ARCHITECTURE_DIAGRAMS.md
    └── ADR_BFF_ARCHITECTURE.md
```

---

## 🔒 Security

- **API Keys:** Different key per BFF, stored in .env
- **Token Validation:** All tokens validated with Pyramid
- **Network:** Internal APIs not exposed publicly
- **TLS:** All inter-service communication over HTTPS
- **Rate Limiting:** Per-BFF and per-user limits

---

## 📈 Success Metrics

- ✅ Zero downtime during migration
- ✅ < 100ms added latency (with caching)
- ✅ 99.9% uptime for BFF services
- ✅ < 5% error rate during migration
- ✅ All existing features work identically

---

## 🚀 Quick Start

1. **Read the plans:**
   - `BFF_MIGRATION_PLAN.md` - Detailed strategy
   - `BFF_QUICK_START.md` - Implementation guide
   - `BFF_ARCHITECTURE_DIAGRAMS.md` - Visual diagrams
   - `ADR_BFF_ARCHITECTURE.md` - Design decisions

2. **Start with Phase 1:**
   ```bash
   cd /path/to/monorepo
   mkdir -p packages/sayaeat-shared/src/{DTOs,Enums,Utils,Contracts,Clients}
   # Follow BFF_QUICK_START.md
   ```

3. **Test as you go:**
   - Test shared package independently
   - Test Pyramid internal APIs with Postman
   - Test each BFF thoroughly before production

4. **Deploy gradually:**
   - Feature flags for traffic routing
   - Start with 10% traffic
   - Monitor and increase gradually

---

## 🆘 Support

- **Technical Lead:** [Your Name]
- **Architecture Questions:** Review ADR_BFF_ARCHITECTURE.md
- **Implementation Help:** Follow BFF_QUICK_START.md
- **Deployment Issues:** Check rollback procedures

---

## 📚 Documentation

All planning documents are in the root of the monorepo:

1. **BFF_SUMMARY.md** (this file) - Executive summary
2. **BFF_MIGRATION_PLAN.md** - Comprehensive migration plan
3. **BFF_QUICK_START.md** - Step-by-step implementation guide
4. **BFF_ARCHITECTURE_DIAGRAMS.md** - Visual architecture diagrams
5. **ADR_BFF_ARCHITECTURE.md** - Architecture decision record

---

## 🎉 Benefits

After migration, you'll have:

✅ **Better Separation of Concerns**
- Each BFF optimized for its client
- Clear service boundaries

✅ **Independent Scaling**
- Scale services based on demand
- Different resource allocations

✅ **Team Autonomy**
- Teams work independently
- Faster development cycles

✅ **Better Performance**
- Client-specific caching
- Optimized responses

✅ **Easier Testing**
- Test BFFs in isolation
- Mock external dependencies

---

**Status:** ✅ Planning Complete  
**Next Step:** Begin Phase 1 (Create Shared Package)  
**Expected Completion:** November 12, 2025

---

**Questions?** Review the detailed plans or contact the platform team.

