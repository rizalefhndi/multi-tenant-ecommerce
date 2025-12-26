# 📝 Task Breakdown - Multi-Tenant E-Commerce

## Legend
- ⬜ Not Started
- 🔄 In Progress  
- ✅ Completed
- 🔴 Blocked

---

# 🎯 PHASE 1: Core E-Commerce ✅ COMPLETED

## Milestone 1.1: Database Schema ✅
**Estimated Time: 2-3 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.1.1 | Create `user_addresses` tenant migration | ✅ | | Alamat pengiriman user |
| 1.1.2 | Create `orders` tenant migration | ✅ | | Order dengan semua status |
| 1.1.3 | Create `order_items` tenant migration | ✅ | | Item dengan product snapshot |
| 1.1.4 | Create `transactions` tenant migration | ✅ | | Payment records |
| 1.1.5 | Add `weight` & `sku` columns to products | ✅ | | Untuk kalkulasi ongkir |
| 1.1.6 | Run migrations for existing tenants | ✅ | | `php artisan tenants:migrate` |

## Milestone 1.2: Eloquent Models ✅
**Estimated Time: 3-4 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.2.1 | Create `UserAddress` model | ✅ | | With relationships & accessors |
| 1.2.2 | Create `Order` model | ✅ | | With status constants & scopes |
| 1.2.3 | Create `OrderItem` model | ✅ | | With product relationship |
| 1.2.4 | Create `Transaction` model | ✅ | | With status management |
| 1.2.5 | Update `User` model - add addresses relationship | ✅ | | `hasMany(UserAddress::class)` |
| 1.2.6 | Update `User` model - add orders relationship | ✅ | | `hasMany(Order::class)` |
| 1.2.7 | Update `Cart` model - convertToOrder method | ⬜ | | Helper untuk checkout |

## Milestone 1.3: Backend Controllers & Routes ✅
**Estimated Time: 4-5 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.3.1 | Create `CheckoutController` | ✅ | | index, process, success |
| 1.3.2 | Create `OrderController` | ✅ | | CRUD + uploadProof + cancel + reorder |
| 1.3.3 | Create `UserAddressController` | ✅ | | CRUD + setDefault + API endpoints |
| 1.3.4 | Create `Admin\OrderController` | ✅ | | Admin order management |
| 1.3.5 | Update `routes/tenant.php` | ✅ | | All routes added |
| 1.3.6 | Create Form Requests | ✅ | | CheckoutRequest, StoreAddressRequest, UpdateAddressRequest |

## Milestone 1.4: Frontend - Checkout Flow ✅
**Estimated Time: 6-8 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.4.1 | Create `Checkout/Index.vue` | ✅ | | Full checkout page with address & payment |
| 1.4.2 | Create `AddressCard.vue` component | ✅ | | Reusable address display component |
| 1.4.3 | Create `AddressFormModal.vue` component | ✅ | | Modal for add/edit address |
| 1.4.4 | Add payment method selection | ✅ | | Bank transfer available |
| 1.4.5 | Create `Checkout/Success.vue` | ✅ | | Order confirmation with confetti |
| 1.4.6 | Update `Carts/Index.vue` | ✅ | | Link to checkout page |
| 1.4.7 | Add Orders navigation | ✅ | | In AuthenticatedLayout |

## Milestone 1.5: Frontend - Orders ✅
**Estimated Time: 4-5 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.5.1 | Create `Orders/Index.vue` | ✅ | | List orders with filter tabs |
| 1.5.2 | Create `Orders/Show.vue` | ✅ | | Full detail with timeline |
| 1.5.3 | Create `Orders/Track.vue` | ✅ | | Tracking page with courier links |
| 1.5.4 | Add upload transfer proof modal | ✅ | | In Orders/Show.vue |
| 1.5.5 | Create `OrderStatusBadge.vue` | ✅ | | Color-coded status badges |
| 1.5.6 | Add navigation in sidebar | ✅ | | Already in Milestone 1.4 |

## Milestone 1.6: Frontend - Addresses ✅
**Estimated Time: 3-4 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.6.1 | Create `AddressCard.vue` | ✅ | | Display with actions (already in 1.4) |
| 1.6.2 | Create `AddressFormModal.vue` | ✅ | | Add/Edit address (already in 1.4) |
| 1.6.3 | Address selection in Checkout | ✅ | | Integrated in Checkout/Index.vue |
| 1.6.4 | Address CRUD in backend | ✅ | | UserAddressController ready |

## Milestone 1.7: Testing ✅
**Estimated Time: 3-4 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 1.7.1 | Unit tests for Order model | ✅ | | tests/Unit/Models/OrderTest.php |
| 1.7.2 | Unit tests for Product model | ✅ | | tests/Unit/Models/ProductTest.php |
| 1.7.3 | Unit tests for Transaction model | ✅ | | tests/Unit/Models/TransactionTest.php |
| 1.7.4 | Feature tests for CheckoutController | ✅ | | tests/Feature/CheckoutControllerTest.php |
| 1.7.5 | Feature tests for OrderController | ✅ | | tests/Feature/OrderControllerTest.php |
| 1.7.6 | Feature tests for UserAddressController | ✅ | | tests/Feature/UserAddressControllerTest.php |

---

# 🎯 PHASE 2: SaaS Business Model ✅ COMPLETED

## Milestone 2.1: Subscription Database ✅
**Estimated Time: 2-3 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 2.1.1 | Create `plans` central migration | ✅ | | 2025_12_26_200001_create_plans_table.php |
| 2.1.2 | Update `tenants` table migration | ✅ | | 2025_12_26_200002_add_subscription_to_tenants_table.php |
| 2.1.3 | Create `tenant_invoices` migration | ✅ | | 2025_12_26_200003_create_tenant_invoices_table.php |
| 2.1.4 | Create `Plan` model | ✅ | | With quotas, features, accessors |
| 2.1.5 | Create `TenantInvoice` model | ✅ | | With invoice generation |
| 2.1.6 | Update `Tenant` model | ✅ | | With subscription management |
| 2.1.7 | Create `PlanSeeder` | ✅ | | 4 plans: Free, Basic, Pro, Enterprise |

## Milestone 2.2: Quota Enforcement ✅
**Estimated Time: 3-4 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 2.2.1 | Create `CheckTenantQuota` middleware | ✅ | | Products, orders, storage |
| 2.2.2 | Create `CheckSubscriptionActive` middleware | ✅ | | Block if inactive |
| 2.2.3 | Register middleware aliases | ✅ | | quota, subscription.active |
| 2.2.4 | Quota checking in Tenant model | ✅ | | canAddProduct, canCreateOrder, canUploadFile |
| 2.2.5 | Usage tracking methods | ✅ | | increment/decrement counters |

## Milestone 2.3: Landlord - Pricing Page ✅
**Estimated Time: 3-4 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 2.3.1 | Create `PricingController` | ✅ | | Landlord controller |
| 2.3.2 | Create `Landlord/Pricing.vue` | ✅ | | Full-featured pricing page |
| 2.3.3 | Add pricing route (/pricing) | ✅ | | Public route |
| 2.3.4 | Toggle monthly/yearly | ✅ | | With savings badge |
| 2.3.5 | FAQ section | ✅ | | Common questions |

## Milestone 2.4: Tenant - Subscription Management ✅
**Estimated Time: 4-5 hours** | **Completed: 26 Dec 2024**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 2.4.1 | Create `SubscriptionController` | ✅ | | Full subscription management |
| 2.4.2 | Create `Subscription/Index.vue` | ✅ | | Status & usage display |
| 2.4.3 | Create `Subscription/Plans.vue` | ✅ | | Plan selection & upgrade |
| 2.4.4 | Create `Subscription/Invoices.vue` | ✅ | | Billing history |
| 2.4.5 | Add subscription routes | ✅ | | In tenant.php |

---

# 🎯 PHASE 3: Payment & Shipping

## Milestone 3.1: Midtrans Integration
**Estimated Time: 5-6 hours**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 3.1.1 | Install `midtrans/midtrans-php` | ⬜ | | composer require |
| 3.1.2 | Create `config/midtrans.php` | ⬜ | | Environment config |
| 3.1.3 | Add Midtrans env variables | ⬜ | | Sandbox keys first |
| 3.1.4 | Create `MidtransService` | ⬜ | | Snap token generation |
| 3.1.5 | Create `MidtransController` | ⬜ | | Callback handling |
| 3.1.6 | Add callback route (webhook) | ⬜ | | POST /webhook/midtrans |
| 3.1.7 | Update CheckoutController | ⬜ | | Generate snap token |
| 3.1.8 | Add Midtrans Snap.js to frontend | ⬜ | | Payment popup |
| 3.1.9 | Handle payment notifications | ⬜ | | Update order status |
| 3.1.10 | Test sandbox transactions | ⬜ | | All payment methods |

## Milestone 3.2: RajaOngkir Integration
**Estimated Time: 4-5 hours**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 3.2.1 | Create RajaOngkir account | ⬜ | | Get API key |
| 3.2.2 | Add config for RajaOngkir | ⬜ | | In services.php |
| 3.2.3 | Create `RajaOngkirService` | ⬜ | | Province, city, cost |
| 3.2.4 | Create `ShippingController` API | ⬜ | | Endpoints for frontend |
| 3.2.5 | Add province/city selects in address form | ⬜ | | Dynamic loading |
| 3.2.6 | Add courier selection in checkout | ⬜ | | JNE, TIKI, POS |
| 3.2.7 | Calculate shipping in checkout | ⬜ | | Based on weight |
| 3.2.8 | Cache province/city data | ⬜ | | 24 hour cache |

---

# 🎯 PHASE 4: Tenant Customization

## Milestone 4.1: Settings Infrastructure
**Estimated Time: 2-3 hours**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 4.1.1 | Create `tenant_settings` migration | ⬜ | | Key-value store |
| 4.1.2 | Create `TenantSetting` model | ⬜ | | With helper methods |
| 4.1.3 | Create `TenantThemeService` | ⬜ | | Get/set theme values |
| 4.1.4 | Create `SettingsController` | ⬜ | | CRUD settings |
| 4.1.5 | Add settings routes | ⬜ | | /settings/* |

## Milestone 4.2: Theme Customization
**Estimated Time: 4-5 hours**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 4.2.1 | Create `Settings/Theme.vue` | ⬜ | | Color pickers, inputs |
| 4.2.2 | Add logo upload with preview | ⬜ | | Max 2MB, auto resize |
| 4.2.3 | Add banner upload | ⬜ | | With crop tool |
| 4.2.4 | Create color picker component | ⬜ | | For primary/secondary/accent |
| 4.2.5 | Implement CSS variable injection | ⬜ | | :root variables |
| 4.2.6 | Update layout to use theme | ⬜ | | Dynamic colors |
| 4.2.7 | Add font selection dropdown | ⬜ | | Google Fonts options |
| 4.2.8 | Create live preview mode | ⬜ | | See changes before save |

## Milestone 4.3: Store Info Settings
**Estimated Time: 2-3 hours**

| # | Task | Status | Assignee | Notes |
|---|------|--------|----------|-------|
| 4.3.1 | Create `Settings/Store.vue` | ⬜ | | Store name, desc, social |
| 4.3.2 | Add social media links inputs | ⬜ | | IG, FB, WA |
| 4.3.3 | Add shipping origin city | ⬜ | | Dropdown dari RajaOngkir |
| 4.3.4 | Display store info in storefront | ⬜ | | Footer, about |

---

# 📊 Summary by Phase

| Phase | Tasks | Est. Hours | Priority |
|-------|-------|------------|----------|
| Phase 1: Core E-Commerce | 35 tasks | 25-33 hrs | 🔴 CRITICAL |
| Phase 2: SaaS Business | 21 tasks | 12-16 hrs | 🟠 HIGH |
| Phase 3: Payment & Shipping | 18 tasks | 9-11 hrs | 🟡 MEDIUM |
| Phase 4: Customization | 16 tasks | 8-11 hrs | 🟢 LOW |
| **TOTAL** | **90 tasks** | **54-71 hrs** | |

---

# 🗓️ Suggested Timeline

## Week 1-2: Phase 1A
- Milestone 1.1 (Database)
- Milestone 1.2 (Models)
- Milestone 1.3 (Controllers)

## Week 2-3: Phase 1B
- Milestone 1.4 (Checkout Frontend)
- Milestone 1.5 (Orders Frontend)
- Milestone 1.6 (Addresses Frontend)
- Milestone 1.7 (Testing)

## Week 3-4: Phase 2
- Milestone 2.1-2.4 (SaaS Features)

## Week 4-5: Phase 3
- Milestone 3.1 (Midtrans)
- Milestone 3.2 (RajaOngkir)

## Week 5-6: Phase 4
- Milestone 4.1-4.3 (Customization)

---

# ⚠️ Dependencies

```
Phase 1.3 (Controllers) depends on → Phase 1.2 (Models)
Phase 1.4 (Checkout UI) depends on → Phase 1.3 (Controllers)
Phase 2.2 (Quota) depends on → Phase 2.1 (Plans DB)
Phase 3.1 (Midtrans) depends on → Phase 1 (Orders exist)
Phase 3.2 (Shipping) depends on → Phase 1.4 (Checkout exists)
Phase 4.2 (Theme) depends on → Phase 4.1 (Settings infra)
```

---

*Last updated: 26 December 2024*
