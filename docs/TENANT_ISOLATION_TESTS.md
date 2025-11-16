# Multi-Tenant Data Isolation Test Suite

## Overview
Test suite specifically to validate **data isolation** and **multi-tenant functionality** in a multi-tenant e‑commerce system.

## Test Results ✅
```
✓ tenant databases are isolated products
✓ tenant databases are isolated users  
✓ multiple tenants can have same user email in different databases
✓ tenant product ids do not conflict
✓ deleting tenant data does not affect other tenants
✓ central database remains separate from tenant data
✓ tenant switch maintains data integrity

Tests: 7 passed (42 assertions)
```

## Test Cases

### 1. **test_tenant_databases_are_isolated_products**
**Purpose:** Ensure products from one tenant are not visible to other tenants

**Scenario:**
- Tenant 1 (shop1) creates product "Shop 1 Laptop" priced at Rp 5.000.000
- Tenant 2 (shop2) creates product "Shop 2 Phone" priced at Rp 3.000.000
- Verify tenant 1 sees only 1 product (its laptop)
- Verify tenant 2 sees only 1 product (its phone)

**Validations:**
✅ Each tenant can only access their own product data  
✅ Product data is stored in separate databases per tenant

---

### 2. **test_tenant_databases_are_isolated_users**
**Purpose:** Ensure users from one tenant are not visible to other tenants

**Scenario:**
- Tenant 1 (company1) creates user "John from Company 1"
- Tenant 2 (company2) creates user "Jane from Company 2"
- Verify tenant 1 sees only John
- Verify tenant 2 sees only Jane

**Validations:**
✅ Each tenant has separate users tables  
✅ User isolation works correctly

---

### 3. **test_multiple_tenants_can_have_same_user_email_in_different_databases**
**Purpose:** Ensure two tenants can have users with the same email

**Scenario:**
- Tenant 1 (store1) creates a user with email "admin@example.com" named "Admin Store 1"
- Tenant 2 (store2) creates a user with the same email "admin@example.com" named "Admin Store 2"
- Both users are created successfully without conflict

**Validations:**
✅ The same email can be used in different tenant databases  
✅ No cross-tenant unique constraint conflicts  
✅ Each tenant uses a separate database

---

### 4. **test_tenant_product_ids_do_not_conflict**
**Purpose:** Ensure product IDs in different tenants do not conflict

**Scenario:**
- Tenant 1 (merchant1) creates "Product A"
- Tenant 2 (merchant2) creates "Product B"
- Both products may have the same ID (auto-increment starting from 1)
- Queries by ID return the correct product for the tenant

**Validations:**
✅ IDs can be identical because databases are separate  
✅ Queries against the correct tenant database always return the expected data  
✅ No data leakage between tenants

---

### 5. **test_deleting_tenant_data_does_not_affect_other_tenants**
**Purpose:** Ensure deleting data in one tenant does not affect other tenants

**Scenario:**
- Tenant 1 (shop_a) creates 2 products: "Product 1" and "Product 2"
- Tenant 2 (shop_b) creates 2 products: "Product X" and "Product Y"
- Delete all products in tenant 1
- Verify tenant 2 still has both of its products

**Validations:**
✅ Delete operations only affect the active tenant's database  
✅ Other tenants' data remains intact and unaffected  
✅ Data isolation is maintained for destructive operations

---

### 6. **test_central_database_remains_separate_from_tenant_data**
**Purpose:** Ensure the central database is separate from tenant databases

**Scenario:**
- Create 2 tenants in the central database (tenants table)
- Create domains for both tenants (domains table)
- Add products to tenant 1 database
- Verify the central database only stores tenant metadata

**Validations:**
✅ Central database stores tenant metadata (id, domains)  
✅ Tenant databases store business data (products, users, carts)  
✅ No mixing of data between central and tenant databases

---

### 7. **test_tenant_switch_maintains_data_integrity**
**Purpose:** Ensure switching between tenants does not break data integrity

**Scenario:**
- Create tenant 1 with product "Switch Product 1" (Rp 111.000, stock 11)
- Create tenant 2 with product "Switch Product 2" (Rp 222.000, stock 22)
- Switch back and forth between tenant 1 and 2 three times
- On each switch verify data remains consistent

**Validations:**
✅ Context switching between tenants works properly  
✅ Data does not get mixed during switches  
✅ Each tenant always receives the correct data after switching

---

## How to Run Tests

### Run all multi-tenant isolation tests:
```bash
php artisan test --filter MultiTenantIsolationTest
```

### Run a specific test:
```bash
php artisan test --filter test_tenant_databases_are_isolated_products
php artisan test --filter test_deleting_tenant_data_does_not_affect_other_tenants
```

### Run with verbose output:
```bash
php artisan test --filter MultiTenantIsolationTest -v
```

---

## Technologies Tested

- **Framework:** Laravel 11  
- **Multi-tenancy Package:** stancl/tenancy  
- **Database:** PostgreSQL (separate databases per tenant)  
- **Testing:** PHPUnit + RefreshDatabase trait  
- **Models:** Tenant, Product, User, Cart, CartItem

---

## Key Features Validated

1. ✅ **Database Isolation** - Each tenant has a separate database  
2. ✅ **Data Privacy** - Tenants cannot access other tenants' data  
3. ✅ **Unique Constraint Separation** - Same email/ID can exist across different tenants  
4. ✅ **Delete Safety** - Delete operations only affect the active tenant  
5. ✅ **Central Database Separation** - Tenant metadata separated from business data  
6. ✅ **Context Switching** - Switching tenants does not corrupt data  
7. ✅ **Query Isolation** - Queries always return data from the correct tenant

---

## Assertions Summary

Total assertions: **42 assertions** across 7 test cases

Average assertions per test: ~6 assertions

Coverage areas:
- Record counts  
- Data values (name, email, price)  
- Relationship integrity  
- Database existence checks  
- Cross-tenant data isolation

---

## Conclusion

✅ **All tests passed** - Data isolation and multi-tenant functionality work as expected.

This multi-tenant e‑commerce system has been validated to:
- Preserve tenant data privacy  
- Prevent data leaks  
- Maintain data integrity during context switching  
- Keep central database separate from tenant databases

**This test suite satisfies the requirement:**
> Write unit tests and/or feature tests to validate data isolation and functionality across multiple databases and tenants.
