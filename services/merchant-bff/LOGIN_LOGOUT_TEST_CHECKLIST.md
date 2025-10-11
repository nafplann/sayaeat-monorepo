# Login/Logout Test Checklist

## Quick Test Steps

### Prerequisites ✓
- [ ] Pyramid service is running
- [ ] Merchant BFF `.env` file is configured
- [ ] `PYRAMID_API_KEY` is set correctly
- [ ] Dependencies are installed (`composer install`)
- [ ] Test database has user records

### Manual Tests

#### Test 1: Login Page
- [ ] Access `http://localhost:8001/` redirects to login
- [ ] Login page renders correctly
- [ ] Form fields are present (email, password)

#### Test 2: Valid Login
- [ ] Enter valid credentials
- [ ] Click login button
- [ ] Redirects to `/manage/dashboard`
- [ ] User data is stored in session
- [ ] Session cookie is set

#### Test 3: Invalid Login
- [ ] Enter invalid credentials
- [ ] Error message displays: "Incorrect email or password"
- [ ] No redirect occurs
- [ ] No session is created

#### Test 4: Form Validation
- [ ] Submit with empty email → validation error
- [ ] Submit with empty password → validation error
- [ ] Submit with invalid email format → validation error

#### Test 5: Protected Routes
- [ ] Access `/manage/dashboard` without login → redirects to login
- [ ] Access `/manage/dashboard` after login → shows dashboard
- [ ] Access `/manage/merchants` after login → shows merchants page

#### Test 6: Get Current User
- [ ] GET `/user` without login → 401 Unauthenticated
- [ ] GET `/user` after login → returns user object

#### Test 7: Logout
- [ ] Click logout button
- [ ] Redirects to `/`
- [ ] Session is cleared
- [ ] Cannot access protected routes after logout

### Automated Tests

```bash
cd services/merchant-bff
php artisan test tests/Feature/AuthTest.php
```

#### Expected Results:
- [ ] All 10 tests pass
- [ ] No errors or warnings
- [ ] Test coverage > 80%

### Integration Tests

#### Pyramid Connection
- [ ] BFF can reach Pyramid API
- [ ] API key authentication works
- [ ] `/internal/auth/validate-credentials` responds correctly
- [ ] User data is returned from Pyramid

### Performance Tests

- [ ] Login completes in < 500ms
- [ ] Dashboard loads in < 200ms after login
- [ ] No memory leaks during multiple login/logout cycles

### Security Tests

- [ ] Passwords are not logged
- [ ] Session regenerated after login
- [ ] Session invalidated after logout
- [ ] CSRF protection works
- [ ] Rate limiting applied to login endpoint

---

## Test Results

**Date:** _________________  
**Tester:** _________________  
**Environment:** _________________  

### Summary
- **Total Tests:** 10 automated + 7 manual = 17 tests
- **Passed:** _____ / 17
- **Failed:** _____ / 17
- **Blocked:** _____ / 17

### Issues Found
1. _______________________________________
2. _______________________________________
3. _______________________________________

### Notes
_________________________________________________
_________________________________________________
_________________________________________________

---

## Sign-off

- [ ] All tests passed
- [ ] No critical issues
- [ ] Ready for next phase (Merchant CRUD testing)

**Approved by:** _________________  
**Date:** _________________

