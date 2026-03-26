# Buyer Onboarding System Architecture

## System Flow Diagram

```mermaid
graph TB
    A[New User Registration] --> B[Email Verification]
    B --> C[Account Type Selection]
    C --> D{Select Account Type}
    
    D -->|Buyer| E[Buyer Category Selection]
    D -->|Seller Types| F[Seller Onboarding Flow]
    
    E --> G{Categories Selected?}
    G -->|No| H[Show Error: Min 1 Required]
    H --> E
    G -->|Yes| I[Save to buyer_categories_selected]
    
    I --> J[Mark buyer_onboarding_completed = true]
    J --> K[Redirect to Dashboard]
    K --> L[Buyer Dashboard View]
    
    L --> M[Browse Filtered Marketplace]
    M --> N{Access Marketplace Section}
    
    N --> O[Professional Services]
    N --> P[Digital Products]
    N --> Q[Growth Marketplace]
    N --> R[Jobs]
    
    O --> S[Filter by Selected Categories]
    P --> S
    Q --> S
    R --> S
    
    S --> T[Display Filtered Results]
    
    L --> U[Settings]
    U --> V[Update Category Preferences]
    V --> E
```

## Component Architecture

```mermaid
graph LR
    subgraph Frontend
        A[Onboarding View]
        B[Settings View]
        C[Dashboard View]
        D[Marketplace Views]
    end
    
    subgraph Controllers
        E[OnboardingController]
        F[SettingsController]
        G[MarketplaceControllers]
    end
    
    subgraph Middleware
        H[EnsureOnboardingCompleted]
        I[EnsureBuyerAccess]
        J[EnsureEarnerAccess]
    end
    
    subgraph Models
        K[User Model]
        L[MarketplaceCategory]
        M[ProfessionalService]
        N[DigitalProduct]
        O[GrowthListing]
        P[Job]
    end
    
    subgraph Database
        Q[(users table)]
        R[(marketplace_categories)]
        S[(Marketplace Tables)]
    end
    
    A --> E
    B --> F
    C --> G
    D --> G
    
    E --> H
    E --> I
    F --> I
    G --> I
    G --> J
    
    E --> K
    F --> K
    G --> K
    
    K --> Q
    K --> L
    L --> R
    
    M --> S
    N --> S
    O --> S
    P --> S
```

## Data Flow: Category Filtering

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant Middleware
    participant Controller
    participant Model
    participant Database
    
    User->>Browser: Access Marketplace
    Browser->>Middleware: HTTP Request
    Middleware->>Model: Check User Account Type
    Model->>Database: Query user.account_type
    Database-->>Model: Return 'buyer'
    Model-->>Middleware: User is Buyer
    
    Middleware->>Model: Get Selected Categories
    Model->>Database: Query buyer_categories_selected
    Database-->>Model: Return [1, 3, 5, 7]
    Model-->>Middleware: Category IDs
    
    Middleware->>Controller: Pass Request with Categories
    Controller->>Model: Query Marketplace Items
    Model->>Database: WHERE category_id IN (1,3,5,7)
    Database-->>Model: Filtered Results
    Model-->>Controller: Return Items
    Controller-->>Browser: Render View with Items
    Browser-->>User: Display Filtered Marketplace
```

## Database Schema

```mermaid
erDiagram
    USERS ||--o{ PROFESSIONAL_SERVICES : "creates/buys"
    USERS ||--o{ DIGITAL_PRODUCTS : "creates/buys"
    USERS ||--o{ GROWTH_LISTINGS : "creates/buys"
    USERS ||--o{ JOBS : "posts/applies"
    
    USERS {
        bigint id PK
        string account_type
        json buyer_categories_selected
        boolean buyer_onboarding_completed
        boolean onboarding_completed
        timestamp created_at
    }
    
    MARKETPLACE_CATEGORIES ||--o{ PROFESSIONAL_SERVICES : "categorizes"
    MARKETPLACE_CATEGORIES ||--o{ DIGITAL_PRODUCTS : "categorizes"
    MARKETPLACE_CATEGORIES ||--o{ GROWTH_LISTINGS : "categorizes"
    MARKETPLACE_CATEGORIES ||--o{ JOBS : "categorizes"
    
    MARKETPLACE_CATEGORIES {
        bigint id PK
        string name
        string slug
        string type
        bigint parent_id FK
        boolean is_active
    }
    
    PROFESSIONAL_SERVICES {
        bigint id PK
        bigint seller_id FK
        bigint category_id FK
        string title
        decimal price
    }
    
    DIGITAL_PRODUCTS {
        bigint id PK
        bigint seller_id FK
        bigint category_id FK
        string title
        decimal price
    }
    
    GROWTH_LISTINGS {
        bigint id PK
        bigint seller_id FK
        bigint category_id FK
        string title
        decimal price
    }
    
    JOBS {
        bigint id PK
        bigint employer_id FK
        bigint category_id FK
        string title
        decimal salary
    }
```

## Middleware Flow

```mermaid
flowchart TD
    A[Incoming Request] --> B{Authenticated?}
    B -->|No| C[Redirect to Login]
    B -->|Yes| D{Email Verified?}
    D -->|No| E[Redirect to Verify Email]
    D -->|Yes| F{Onboarding Completed?}
    
    F -->|No| G[EnsureOnboardingCompleted]
    G --> H{Account Type Selected?}
    H -->|No| I[Redirect to Select Type]
    H -->|Yes| J{Is Buyer?}
    
    J -->|Yes| K{Categories Selected?}
    K -->|No| L[Redirect to Category Selection]
    K -->|Yes| M[Continue to Route]
    
    J -->|No| N{Is Seller?}
    N -->|Yes| O[EnsureEarnerAccess]
    O --> P{Activation Paid?}
    P -->|No| Q[Redirect to Activation]
    P -->|Yes| M
    
    N -->|No| M
    F -->|Yes| R{Accessing Marketplace?}
    R -->|Yes| S[EnsureBuyerAccess]
    S --> T{Is Buyer?}
    T -->|Yes| U[Apply Category Filter]
    U --> M
    T -->|No| M
    R -->|No| M
```

## Category Selection UI Structure

```
┌─────────────────────────────────────────────────────────────┐
│                  Buyer Category Selection                    │
│         Choose categories you're interested in               │
│              (Minimum 1 category required)                   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Professional Services                    [Select All]       │
├─────────────────────────────────────────────────────────────┤
│  ☐ Web Development & Programming                            │
│  ☐ Graphic Design & Branding                                │
│  ☐ Content Writing & Copywriting                            │
│  ☐ Digital Marketing & SEO                                  │
│  ☐ Video Editing & Animation                                │
│  ☐ Virtual Assistant Services                               │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Digital Products                         [Select All]       │
├─────────────────────────────────────────────────────────────┤
│  ☐ Software & Plugins                                       │
│  ☐ Website Templates & Themes                               │
│  ☐ E-books & Digital Guides                                 │
│  ☐ Online Courses & Tutorials                               │
│  ☐ Graphics & Design Assets                                 │
│  ☐ Music & Audio Files                                      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Growth Marketplace                       [Select All]       │
├─────────────────────────────────────────────────────────────┤
│  ☐ Backlinks & SEO Services                                 │
│  ☐ Social Media Growth                                      │
│  ☐ Lead Generation                                          │
│  ☐ Traffic & Advertising                                    │
│  ☐ Influencer Marketing                                     │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Jobs & Opportunities                     [Select All]       │
├─────────────────────────────────────────────────────────────┤
│  ☐ Remote Jobs                                              │
│  ☐ Freelance Projects                                       │
│  ☐ Part-time Opportunities                                  │
│  ☐ Internships                                              │
└─────────────────────────────────────────────────────────────┘

                    [Continue to Dashboard]
                    
Selected: 0 categories (Minimum 1 required)
```

## Access Control Matrix

| Account Type | Activation Fee | Category Selection | Browse All | Create Listings | Filtered View |
|--------------|----------------|-------------------|------------|-----------------|---------------|
| Earner       | ✅ Required    | ❌ No             | ❌ No      | ❌ No           | ❌ No         |
| Task Creator | ❌ No          | ❌ No             | ✅ Yes     | ✅ Yes          | ❌ No         |
| Freelancer   | ✅ Required    | ❌ No             | ✅ Yes     | ✅ Yes          | ❌ No         |
| Digital Seller| ✅ Required   | ❌ No             | ✅ Yes     | ✅ Yes          | ❌ No         |
| Growth Seller| ✅ Required    | ❌ No             | ✅ Yes     | ✅ Yes          | ❌ No         |
| **Buyer**    | **❌ No**      | **✅ Yes**        | **❌ No**  | **❌ No**       | **✅ Yes**    |

## Implementation Phases

### Phase 1: Database & Models
- Create migration for buyer fields
- Update User model with methods
- Test database operations

### Phase 2: Middleware & Access Control
- Create EnsureBuyerAccess middleware
- Update EnsureEarnerAccess
- Register middleware in Kernel
- Test access control logic

### Phase 3: Onboarding Flow
- Update OnboardingController
- Create category selection view
- Add validation logic
- Test onboarding flow

### Phase 4: Marketplace Filtering
- Update all marketplace controllers
- Add category filtering queries
- Test filtered results
- Verify performance

### Phase 5: Settings & Management
- Add category management to SettingsController
- Create settings view
- Add update functionality
- Test category updates

### Phase 6: Dashboard & UI
- Update dashboard for buyers
- Add category indicators
- Create buyer-specific widgets
- Polish UI/UX

### Phase 7: Testing & Deployment
- End-to-end testing
- Performance testing
- Security audit
- Documentation
- Deployment

## Security Considerations

### Input Validation
- Validate category IDs exist in database
- Ensure minimum 1 category selected
- Prevent SQL injection in category queries
- Sanitize user inputs

### Access Control
- Verify user is authenticated
- Check account type before filtering
- Prevent unauthorized category access
- Rate limit category updates

### Data Integrity
- Use transactions for category updates
- Validate foreign key constraints
- Handle edge cases (deleted categories)
- Maintain audit trail

## Performance Optimization

### Database Queries
- Index `buyer_categories_selected` JSON field
- Cache category lists
- Use eager loading for relationships
- Optimize WHERE IN queries

### Caching Strategy
- Cache marketplace categories
- Cache user category selections
- Invalidate cache on updates
- Use Redis for session data

### Query Optimization
```sql
-- Efficient category filtering
SELECT * FROM professional_services 
WHERE category_id IN (
    SELECT value FROM json_each(
        (SELECT buyer_categories_selected FROM users WHERE id = ?)
    )
)
AND is_active = 1
ORDER BY created_at DESC
LIMIT 20;
```

## Error Handling

### Common Scenarios
1. **No categories selected**: Show validation error
2. **Invalid category ID**: Filter out and show warning
3. **All categories deleted**: Redirect to category selection
4. **Database error**: Show friendly error message
5. **Session timeout**: Redirect to login

### User Feedback
- Clear error messages
- Success confirmations
- Loading indicators
- Helpful tooltips

## Monitoring & Analytics

### Metrics to Track
- Category selection distribution
- Most popular categories
- Buyer conversion rates
- Time to complete onboarding
- Category update frequency

### Logging
- Log category selections
- Track onboarding completion
- Monitor filter performance
- Audit category changes

---

This architecture ensures a scalable, maintainable, and user-friendly buyer onboarding system that integrates seamlessly with the existing SwiftKudi platform.
