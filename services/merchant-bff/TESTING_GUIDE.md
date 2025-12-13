# Merchant BFF Testing Guide

## Overview
This guide provides comprehensive testing procedures for the Merchant BFF authentication and functionality.

## Prerequisites

### 1. Environment Setup
```bash
cd services/merchant-bff

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Update .env with Pyramid configuration
# PYRAMID_API_URL=http://pyramid:8000/api
# PYRAMID_API_KEY=your-pyramid-api-key-here
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Build frontend assets
npm run build
```

### 3. Setup Pyramid Service
Ensure Pyramid service is running and accessible at the configured URL.

## Testing Authentication Flow

### Test 1: Login Page Access

**Test Case:** Access login page
```bash
# Method 1: Browser
Open: http://localhost:8001/

# Method 2: cURL
curl -v http://localhost:8001/
```

**Expected Results:**
- Status: 200 OK
- Response: Login page HTML
- View: `auth.login` blade template

---

### Test 2: Valid Login

**Test Case:** Login with valid credentials

#### Using Browser:
1. Navigate to `http://localhost:8001/`
2. Enter valid credentials:
   - Email: `admin@sayaeat.com` (or your test user)
   - Password: `your-password`
3. Click "Login"

#### Using cURL:
```bash
curl -X POST http://localhost:8001/loginRequest \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@sayaeat.com",
    "password": "your-password"
  }' \
  -c cookies.txt
```

**Expected Results:**
- Status: 200 OK
- Response JSON:
  ```json
  {
    "status": true,
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@sayaeat.com",
      ...
    },
    "redirectTo": "http://localhost:8001/manage/dashboard"
  }
  ```
- Session cookie set
- User data stored in session

**Backend Flow:**
1. BFF receives login request
2. BFF calls `POST /internal/auth/validate-credentials` on Pyramid
3. Pyramid validates credentials against database
4. Pyramid returns user data
5. BFF stores user in session
6. BFF returns success response

---

### Test 3: Invalid Login

**Test Case:** Login with invalid credentials

```bash
curl -X POST http://localhost:8001/loginRequest \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "wrong@example.com",
    "password": "wrongpassword"
  }'
```

**Expected Results:**
- Status: 401 Unauthorized
- Response JSON:
  ```json
  {
    "status": false,
    "message": "Incorrect email or password"
  }
  ```
- No session created
- No cookies set

---

### Test 4: Login Validation

**Test Case:** Login with missing fields

#### Missing Email:
```bash
curl -X POST http://localhost:8001/loginRequest \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "password": "password123"
  }'
```

**Expected Results:**
- Status: 422 Unprocessable Entity
- Validation error for email field

#### Missing Password:
```bash
curl -X POST http://localhost:8001/loginRequest \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com"
  }'
```

**Expected Results:**
- Status: 422 Unprocessable Entity
- Validation error for password field

#### Invalid Email Format:
```bash
curl -X POST http://localhost:8001/loginRequest \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "not-an-email",
    "password": "password123"
  }'
```

**Expected Results:**
- Status: 422 Unprocessable Entity
- Validation error for email format

---

### Test 5: Access Protected Route (Authenticated)

**Test Case:** Access dashboard after login

```bash
# After successful login (with cookies)
curl -X GET http://localhost:8001/manage/dashboard \
  -H "Accept: text/html" \
  -b cookies.txt
```

**Expected Results:**
- Status: 200 OK
- Response: Dashboard HTML page
- User can see dashboard content

---

### Test 6: Access Protected Route (Unauthenticated)

**Test Case:** Access dashboard without login

```bash
curl -X GET http://localhost:8001/manage/dashboard \
  -H "Accept: text/html"
```

**Expected Results:**
- Status: 302 Found (Redirect)
- Location: `/auth/login`
- User redirected to login page

---

### Test 7: Get Current User

**Test Case:** Retrieve current authenticated user

```bash
# With valid session
curl -X GET http://localhost:8001/user \
  -H "Accept: application/json" \
  -b cookies.txt
```

**Expected Results (Authenticated):**
- Status: 200 OK
- Response JSON:
  ```json
  {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@sayaeat.com",
      ...
    }
  }
  ```

**Expected Results (Unauthenticated):**
- Status: 401 Unauthorized
- Response JSON:
  ```json
  {
    "error": "Unauthenticated"
  }
  ```

---

### Test 8: Logout

**Test Case:** Logout authenticated user

#### Using Browser:
1. After login, click "Logout" button
2. Or navigate to `/auth/logout`

#### Using cURL:
```bash
curl -X GET http://localhost:8001/auth/logout \
  -H "Accept: text/html" \
  -b cookies.txt \
  -c cookies.txt
```

**Expected Results:**
- Status: 302 Found (Redirect)
- Location: `/`
- Session cleared
- User data removed from session
- Session cookie invalidated

**Verification:**
Try accessing protected route after logout:
```bash
curl -X GET http://localhost:8001/manage/dashboard \
  -H "Accept: text/html" \
  -b cookies.txt
```
Should redirect to login page.

---

## Testing Pyramid Integration

### Test 9: Pyramid API Connection

**Test Case:** Verify BFF can communicate with Pyramid

**Check Pyramid Internal API:**
```bash
# Test Pyramid internal auth endpoint directly
curl -X POST http://pyramid:8000/api/internal/auth/validate-credentials \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: your-pyramid-api-key-here" \
  -d '{
    "email": "admin@sayaeat.com",
    "password": "your-password"
  }'
```

**Expected Results:**
- Status: 200 OK
- Response JSON:
  ```json
  {
    "valid": true,
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@sayaeat.com",
      ...
    }
  }
  ```

---

### Test 10: Pyramid API Key Validation

**Test Case:** Verify API key authentication

**Without API Key:**
```bash
curl -X POST http://pyramid:8000/api/internal/auth/validate-credentials \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sayaeat.com",
    "password": "your-password"
  }'
```

**Expected Results:**
- Status: 401 Unauthorized
- Error: "Unauthorized" or similar

**With Invalid API Key:**
```bash
curl -X POST http://pyramid:8000/api/internal/auth/validate-credentials \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: invalid-key" \
  -d '{
    "email": "admin@sayaeat.com",
    "password": "your-password"
  }'
```

**Expected Results:**
- Status: 401 Unauthorized
- Error: "Unauthorized"

---

## Running Automated Tests

### Unit Tests
```bash
cd services/merchant-bff

# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=AuthTest

# Run with coverage
php artisan test --coverage
```

### Feature Tests
```bash
# Run feature tests only
php artisan test tests/Feature/

# Run auth tests specifically
php artisan test tests/Feature/AuthTest.php

# Run with detailed output
php artisan test --filter=AuthTest -v
```

### Test Output Expected:
```
PASS  Tests\Feature\AuthTest
✓ login page loads
✓ successful login with valid credentials
✓ login failure with invalid credentials
✓ login validation errors
✓ successful logout
✓ protected route requires authentication
✓ protected route with authentication
✓ get current user
✓ get current user unauthenticated
✓ pyramid api connection failure

Tests:  10 passed
Time:   0.45s
```

---

## Common Issues & Troubleshooting

### Issue 1: "Connection refused" to Pyramid
**Symptoms:** Cannot connect to Pyramid API
**Solutions:**
1. Verify Pyramid is running: `docker ps | grep pyramid`
2. Check `PYRAMID_API_URL` in `.env`
3. Verify network connectivity between services
4. Check Pyramid logs: `docker logs pyramid`

### Issue 2: "Unauthorized" from Pyramid
**Symptoms:** 401 errors when calling Pyramid internal APIs
**Solutions:**
1. Verify `PYRAMID_API_KEY` is set in `.env`
2. Check Pyramid's `config/services.php` for valid API keys
3. Ensure X-Api-Key header is being sent

### Issue 3: Session not persisting
**Symptoms:** User redirected to login after successful authentication
**Solutions:**
1. Check `SESSION_DRIVER` in `.env` (should be `cookie` or `redis`)
2. Verify session configuration in `config/session.php`
3. Clear session cache: `php artisan cache:clear`
4. Check browser cookies are enabled

### Issue 4: CSRF token mismatch
**Symptoms:** 419 errors on form submission
**Solutions:**
1. Ensure `@csrf` directive in forms
2. Check `APP_KEY` is set in `.env`
3. Clear config cache: `php artisan config:clear`

---

## Performance Testing

### Response Time Benchmarks

**Target Metrics:**
- Login request: < 500ms (including Pyramid call)
- Protected route access: < 100ms (with cached user data)
- Logout: < 50ms

**Load Testing:**
```bash
# Using Apache Bench
ab -n 1000 -c 10 -p login.json -T application/json http://localhost:8001/loginRequest

# Using wrk
wrk -t4 -c100 -d30s --latency http://localhost:8001/manage/dashboard
```

---

## Security Checklist

- [ ] Passwords are never logged
- [ ] Session IDs are regenerated after login
- [ ] Session is invalidated after logout
- [ ] HTTPS is enforced in production
- [ ] CSRF protection is enabled
- [ ] API keys are stored in environment variables
- [ ] Sensitive data is not cached
- [ ] Rate limiting is applied to login endpoint

---

## Test Data

### Test Users
Create test users in Pyramid database:

```sql
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@test.com', '$2y$12$...', 'admin'),
('Merchant User', 'merchant@test.com', '$2y$12$...', 'merchant'),
('Regular User', 'user@test.com', '$2y$12$...', 'user');
```

---

## Next Steps

After completing login/logout tests:
1. Test merchant CRUD operations
2. Test menu management
3. Test order management
4. Test store management
5. Integration testing with frontend
6. Load testing
7. Security audit

---

## Report Issues

When reporting issues, include:
1. Test case being executed
2. Expected vs actual results
3. Error logs from BFF (`storage/logs/laravel.log`)
4. Error logs from Pyramid
5. Request/response details (headers, body)
6. Environment details (.env configuration)

