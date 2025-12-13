# BFF Architecture - Visual Diagrams

This document contains visual diagrams for the BFF architecture migration.

---

## 1. High-Level Architecture

```mermaid
graph TB
    subgraph "Clients"
        MW[Merchant Web Portal]
        MA[Hapi Mobile App<br/>Customer]
        DA[Horus Mobile App<br/>Driver]
    end

    subgraph "BFF Layer"
        MBFF[Merchant BFF<br/>Session Auth]
        HBFF[Hapi BFF<br/>Token Auth]
    end

    subgraph "Data Layer"
        PYR[Pyramid Data Service<br/>+ Driver Routes<br/>Internal APIs]
    end

    subgraph "Shared"
        PKG[sayaeat/shared<br/>Enums, DTOs, Utils]
    end

    subgraph "Infrastructure"
        REDIS[(Redis Cache)]
        DB[(PostgreSQL)]
    end

    MW -->|HTTP| MBFF
    MA -->|HTTP| HBFF
    DA -->|HTTP| PYR
    
    MBFF -->|API Key| PYR
    HBFF -->|API Key| PYR
    
    MBFF -.->|uses| PKG
    HBFF -.->|uses| PKG
    PYR -.->|uses| PKG
    
    MBFF -->|cache| REDIS
    HBFF -->|cache| REDIS
    
    PYR -->|read/write| DB

    style MBFF fill:#90EE90
    style HBFF fill:#87CEEB
    style PYR fill:#FFB6C1
    style PKG fill:#FFE4B5
```

---

## 2. Authentication Flow - Merchant BFF

```mermaid
sequenceDiagram
    participant User
    participant MerchantBFF
    participant Redis
    participant Pyramid
    participant Database

    User->>MerchantBFF: POST /auth/login<br/>(email, password)
    MerchantBFF->>Pyramid: POST /internal/auth/validate-credentials<br/>X-Api-Key: merchant-key
    Pyramid->>Database: SELECT * FROM users<br/>WHERE email = ?
    Database-->>Pyramid: User record
    Pyramid->>Pyramid: Verify password hash
    Pyramid-->>MerchantBFF: {valid: true, user: {...}}
    MerchantBFF->>MerchantBFF: Create session
    MerchantBFF->>Redis: Store session data
    Redis-->>MerchantBFF: OK
    MerchantBFF-->>User: Session cookie + redirect

    Note over User,MerchantBFF: Subsequent requests

    User->>MerchantBFF: GET /merchants<br/>(session cookie)
    MerchantBFF->>MerchantBFF: Validate session
    MerchantBFF->>Pyramid: GET /internal/merchants<br/>X-Api-Key: merchant-key
    Pyramid->>Database: SELECT * FROM merchants
    Database-->>Pyramid: Merchants data
    Pyramid-->>MerchantBFF: [{id: 1, name: "..."}, ...]
    MerchantBFF-->>User: HTML page with merchants
```

---

## 3. Authentication Flow - Hapi BFF (Token-Based)

```mermaid
sequenceDiagram
    participant Mobile as Mobile App
    participant HapiBFF as Hapi BFF
    participant Redis
    participant Pyramid
    participant Database

    Note over Mobile,Database: Login Flow

    Mobile->>HapiBFF: POST /api/v1/auth/login<br/>(phone)
    HapiBFF->>Pyramid: POST /internal/auth/send-otp<br/>X-Api-Key: hapi-key
    Pyramid->>Database: Store OTP
    Pyramid->>Pyramid: Send SMS
    Pyramid-->>HapiBFF: {success: true}
    HapiBFF-->>Mobile: {success: true}

    Mobile->>HapiBFF: POST /api/v1/auth/verify-otp<br/>(phone, otp)
    HapiBFF->>Pyramid: POST /internal/auth/verify-otp<br/>X-Api-Key: hapi-key
    Pyramid->>Database: Validate OTP
    Pyramid->>Database: Create Sanctum token
    Pyramid-->>HapiBFF: {token: "xxx", user: {...}}
    HapiBFF-->>Mobile: {token: "xxx", user: {...}}

    Note over Mobile,Database: Authenticated Requests

    Mobile->>HapiBFF: GET /api/v1/orders<br/>Bearer: token-xxx
    HapiBFF->>Redis: GET token:token-xxx
    Redis-->>HapiBFF: miss
    HapiBFF->>Pyramid: POST /internal/auth/validate-token<br/>X-Api-Key: hapi-key<br/>{token: "token-xxx"}
    Pyramid->>Database: SELECT * FROM personal_access_tokens
    Database-->>Pyramid: Token record
    Pyramid-->>HapiBFF: {valid: true, user_id: 123}
    HapiBFF->>Redis: SET token:token-xxx = 123<br/>EX 600
    Redis-->>HapiBFF: OK
    HapiBFF->>Pyramid: GET /internal/orders?user_id=123<br/>X-Api-Key: hapi-key
    Pyramid->>Database: SELECT * FROM orders
    Database-->>Pyramid: Orders data
    Pyramid-->>HapiBFF: [{id: 1, ...}, ...]
    HapiBFF-->>Mobile: JSON orders
```

---

## 4. Data Flow - Creating an Order

```mermaid
sequenceDiagram
    participant Mobile as Mobile App
    participant HapiBFF as Hapi BFF
    participant Redis
    participant Pyramid
    participant Database
    participant Queue

    Mobile->>HapiBFF: POST /api/v1/orders/submit<br/>Bearer: token-xxx<br/>{merchant_id, items, ...}
    
    HapiBFF->>Redis: GET token:token-xxx
    Redis-->>HapiBFF: user_id: 123 (cache hit)
    
    HapiBFF->>HapiBFF: Validate request data
    
    HapiBFF->>Pyramid: POST /internal/orders<br/>X-Api-Key: hapi-key<br/>{user_id: 123, merchant_id, items}
    
    Pyramid->>Database: BEGIN TRANSACTION
    Pyramid->>Database: INSERT INTO orders
    Pyramid->>Database: INSERT INTO order_items
    Pyramid->>Database: UPDATE merchant inventory
    Pyramid->>Database: COMMIT
    
    Pyramid->>Queue: Dispatch OrderCreated event
    Queue-->>Pyramid: Queued
    
    Pyramid-->>HapiBFF: {order: {id: 456, status: "pending"}}
    
    HapiBFF->>Redis: DEL user:123:orders (invalidate cache)
    
    HapiBFF-->>Mobile: {order: {id: 456, status: "pending"}}
    
    Note over Queue,Database: Async Processing
    
    Queue->>Pyramid: Process OrderCreated job
    Pyramid->>Pyramid: Send notification to merchant
    Pyramid->>Pyramid: Send notification to driver
```

---

## 5. Current vs. Future Architecture

### Current (Monolith)

```mermaid
graph LR
    subgraph "Clients"
        MW[Merchant Web]
        MA[Mobile App]
        DA[Driver App]
    end

    subgraph "Pyramid Monolith"
        WEB[Web Routes]
        API[API Routes]
        CTL[Controllers]
        MDL[Models]
        DB[(Database)]
    end

    MW -->|HTTP| WEB
    MA -->|HTTP| API
    DA -->|HTTP| API
    
    WEB --> CTL
    API --> CTL
    CTL --> MDL
    MDL --> DB

    style MW fill:#FFE4B5
    style MA fill:#FFE4B5
    style DA fill:#FFE4B5
    style WEB fill:#FFB6C1
    style API fill:#FFB6C1
    style CTL fill:#FFB6C1
```

### Future (BFF Pattern)

```mermaid
graph TB
    subgraph "Clients"
        MW[Merchant Web]
        MA[Mobile App]
        DA[Driver App]
    end

    subgraph "BFF Layer"
        MBFF[Merchant BFF]
        HBFF[Hapi BFF]
    end

    subgraph "Data Layer"
        PYR[Pyramid<br/>Internal APIs]
        MDL[Models]
        DB[(Database)]
    end

    subgraph "Shared"
        PKG[sayaeat/shared]
    end

    MW -->|HTTP| MBFF
    MA -->|HTTP| HBFF
    DA -->|HTTP| PYR
    
    MBFF -->|API Key| PYR
    HBFF -->|API Key| PYR
    
    MBFF -.-> PKG
    HBFF -.-> PKG
    PYR -.-> PKG
    
    PYR --> MDL
    MDL --> DB

    style MBFF fill:#90EE90
    style HBFF fill:#87CEEB
    style PYR fill:#FFB6C1
    style PKG fill:#FFE4B5
```

---

## 6. Caching Strategy

```mermaid
graph TB
    subgraph "Request Flow with Cache"
        REQ[Request]
        BFF[BFF Service]
        REDIS[(Redis Cache)]
        PYR[Pyramid API]
        
        REQ --> BFF
        BFF --> CHK{Cache Hit?}
        CHK -->|Yes| RET[Return Cached Data]
        CHK -->|No| PYR
        PYR --> SAVE[Save to Cache]
        SAVE --> RET
    end

    subgraph "Cache Keys"
        TK[token:xxx<br/>TTL: 10 min]
        USR[user:123<br/>TTL: 10 min]
        MER[merchant:456<br/>TTL: 5 min]
        MENU[menu:456<br/>TTL: 5 min]
    end

    subgraph "Invalidation"
        UPD[Data Updated]
        INV[Invalidate Cache]
        WH[Webhook to BFFs<br/>future]
        
        UPD --> INV
        INV -.-> WH
    end

    style RET fill:#90EE90
    style PYR fill:#FFB6C1
```

---

## 7. Migration Phases

```mermaid
gantt
    title BFF Migration Timeline
    dateFormat YYYY-MM-DD
    section Phase 1
    Create Shared Package           :p1, 2025-10-11, 3d
    Setup Package Structure         :p2, 2025-10-11, 2d
    Copy Enums & Utils             :p3, 2025-10-13, 1d
    Test Shared Package            :p4, 2025-10-14, 1d
    
    section Phase 2
    Pyramid Internal APIs          :p5, 2025-10-14, 5d
    API Key Middleware             :p6, 2025-10-14, 1d
    Auth Controllers               :p7, 2025-10-15, 2d
    CRUD Controllers               :p8, 2025-10-16, 3d
    Test Internal APIs             :p9, 2025-10-18, 1d
    
    section Phase 3
    Merchant BFF Setup             :p10, 2025-10-18, 10d
    Install Dependencies           :p11, 2025-10-18, 1d
    Copy Routes & Controllers      :p12, 2025-10-19, 3d
    Implement Services             :p13, 2025-10-21, 3d
    Copy Views & Assets            :p14, 2025-10-23, 2d
    Testing & QA                   :p15, 2025-10-25, 2d
    Deploy to Production           :p16, 2025-10-27, 1d
    
    section Phase 4
    Hapi BFF Setup                 :p17, 2025-10-28, 10d
    Install Dependencies           :p18, 2025-10-28, 1d
    Copy API Routes & Controllers  :p19, 2025-10-29, 3d
    Token Validation               :p20, 2025-10-31, 2d
    Implement Services             :p21, 2025-11-02, 3d
    Testing & QA                   :p22, 2025-11-05, 2d
    Deploy to Production           :p23, 2025-11-07, 1d
    
    section Phase 5
    Cleanup & Optimization         :p24, 2025-11-08, 5d
    Remove Old Code                :p25, 2025-11-08, 2d
    Performance Tuning             :p26, 2025-11-10, 2d
    Documentation                  :p27, 2025-11-12, 1d
```

---

## 8. Service Communication Patterns

```mermaid
graph TB
    subgraph "Synchronous (Current Plan)"
        BFF1[BFF] -->|REST API<br/>API Key| PYR1[Pyramid]
        PYR1 -->|Response| BFF1
    end

    subgraph "Future: Async with Events"
        BFF2[BFF] -->|Command| QUEUE[Message Queue]
        QUEUE -->|Consume| PYR2[Pyramid]
        PYR2 -->|Event| QUEUE2[Event Bus]
        QUEUE2 -->|Subscribe| BFF2
    end

    subgraph "Future: GraphQL"
        MOBILE[Mobile] -->|GraphQL Query| BFF3[BFF]
        BFF3 -->|Multiple REST| PYR3[Pyramid]
        PYR3 -->|Aggregated| BFF3
        BFF3 -->|Single Response| MOBILE
    end

    style BFF1 fill:#90EE90
    style PYR1 fill:#FFB6C1
    style BFF2 fill:#87CEEB
    style BFF3 fill:#DDA0DD
```

---

## 9. Error Handling & Retry Logic

```mermaid
sequenceDiagram
    participant BFF
    participant Redis
    participant Pyramid
    
    BFF->>Pyramid: GET /internal/merchants
    Pyramid-->>BFF: 500 Internal Error
    
    Note over BFF: Retry 1
    BFF->>Pyramid: GET /internal/merchants
    Pyramid-->>BFF: Timeout
    
    Note over BFF: Retry 2
    BFF->>Pyramid: GET /internal/merchants
    Pyramid-->>BFF: 503 Service Unavailable
    
    Note over BFF: Retry 3
    BFF->>Pyramid: GET /internal/merchants
    Pyramid-->>BFF: 200 OK + data
    
    BFF->>Redis: Cache response
    BFF->>BFF: Return success
    
    Note over BFF,Pyramid: If all retries fail
    
    BFF->>Redis: Check stale cache
    alt Stale cache exists
        Redis-->>BFF: Stale data
        BFF->>BFF: Return stale data<br/>with warning
    else No cache
        BFF->>BFF: Return error response
    end
```

---

## 10. Deployment Strategy

```mermaid
graph TB
    subgraph "Week 1: Deploy BFF"
        DEP1[Deploy Merchant BFF]
        DEP2[0% Traffic]
        DEP3[Health Checks]
    end

    subgraph "Week 2: Testing"
        TEST1[Integration Tests]
        TEST2[Route 10% Traffic]
        TEST3[Monitor Metrics]
    end

    subgraph "Week 3: Gradual Rollout"
        ROLL1[25% Traffic]
        ROLL2[50% Traffic]
        ROLL3[75% Traffic]
    end

    subgraph "Week 4: Full Migration"
        FULL1[100% Traffic]
        FULL2[Remove Old Routes]
        FULL3[Cleanup]
    end

    subgraph "Rollback Plan"
        ERROR[High Error Rate]
        ROLL[Feature Flag OFF]
        BACK[Route to Pyramid]
    end

    DEP1 --> DEP2 --> DEP3
    DEP3 --> TEST1 --> TEST2 --> TEST3
    TEST3 --> ROLL1 --> ROLL2 --> ROLL3
    ROLL3 --> FULL1 --> FULL2 --> FULL3
    
    TEST3 -.->|If errors| ERROR
    ROLL1 -.->|If errors| ERROR
    ROLL2 -.->|If errors| ERROR
    ERROR --> ROLL --> BACK

    style FULL1 fill:#90EE90
    style ERROR fill:#FF6B6B
    style BACK fill:#FFE4B5
```

---

## Legend

- 🟢 Green: BFF Services
- 🔵 Blue: Hapi BFF
- 🔴 Pink: Pyramid Data Service
- 🟡 Yellow: Shared Components
- ⚪ Gray: Infrastructure

---

**Note:** These diagrams can be rendered using Mermaid in GitHub, GitLab, or documentation tools that support Mermaid syntax.

**Last Updated:** October 11, 2025

