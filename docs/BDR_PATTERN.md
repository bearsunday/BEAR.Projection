# BDR Pattern (Business Domain Repository Pattern)

## Core Concept

The BDR Pattern represents a paradigm where **SQL and Object-Oriented Programming work in harmony**. Rather than forcing one to imitate the other, each excels in its own domain.

- **SQL remains SQL** - Complex queries, JOINs, and optimizations operate at maximum performance
- **Objects remain objects** - Domain models are autonomous entities with behavior, not mere data containers

## Why "Projection"?

In CQRS terminology, a **Projection** is a read-optimized view of data—a specific "projection" of the underlying state onto a screen or API response.

Greg Young, architect of CQRS, stated that Query models should be treated as **"disposable"**. Projections are not sacred domain models requiring permanent maintenance. They are snapshots optimized for specific screens at specific moments. When screens change, throw them away and rebuild.

This frees developers from:
- Complex ORM mappings
- Lazy loading concerns
- Serialization battles
- The burden of making Query models "architecturally correct"

See: [CQRS and Immutable Data Model](https://qiita.com/koriym/items/4e52758d5327447fed13)

## Three-Layer Architecture

```
┌─────────────────────────────────────────────────────────┐
│  SQL Layer                                              │
│  user_profile.sql                                       │
│  ┌───────────────────────────────────────────────────┐  │
│  │ SELECT id, name, birth_date, avatar_path          │  │
│  │ FROM users WHERE id = :id                         │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│  Factory Layer (with DI)                                │
│  UserProfileFactory                                     │
│  ┌───────────────────────────────────────────────────┐  │
│  │ - AgeCalculator (injected)                        │  │
│  │ - ImageUrlResolver (injected)                     │  │
│  │                                                   │  │
│  │ Transform: birth_date → age                       │  │
│  │ Transform: avatar_path → full URL                 │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│  Domain Object Layer                                    │
│  UserProfile (immutable)                                │
│  ┌───────────────────────────────────────────────────┐  │
│  │ + id: string                                      │  │
│  │ + name: string                                    │  │
│  │ + age: int                                        │  │
│  │ + avatarUrl: string                               │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### SQL Layer

Optimized queries in separate `.sql` files. No ORM abstraction obscures the actual database operations.

```sql
-- var/sql/query/user_profile.sql
SELECT id, name, birth_date, avatar_path
FROM users
WHERE id = :id
```

### Factory Layer

Transforms raw query results into domain objects. Dependencies are injected for data transformation and validation.

```php
final class UserProfileFactory
{
    public function __construct(
        private readonly AgeCalculator $ageCalculator,
        private readonly ImageUrlResolver $imageResolver,
    ) {
    }

    public function __invoke(
        string $id,
        string $name,
        string $birthDate,
        string $avatarPath,
    ): UserProfile {
        return new UserProfile(
            id: $id,
            name: $name,
            age: $this->ageCalculator->fromBirthDate($birthDate),
            avatarUrl: $this->imageResolver->resolve($avatarPath),
        );
    }
}
```

### Domain Object Layer

Immutable, read-only objects representing database snapshots. No setters, no `save()` methods.

```php
final class UserProfile
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $age,
        public readonly string $avatarUrl,
    ) {
    }
}
```

## Why This Pattern?

### Clear Separation of Concerns

Each layer has a single responsibility:
- SQL: Data retrieval optimization
- Factory: Transformation and dependency injection
- Domain Object: Type-safe representation

### Independent Testing

Each layer tests independently:
- SQL layer verifies data retrieval
- Factory layer confirms transformation logic
- Domain object layer validates structure

### Transparent to AI Tools

Explicit dependencies, visible SQL, and traceable logic flows enable better automation compared to opaque ORM abstractions.

### No "Simple CRUD" Illusion

Every application eventually needs:
- Display formatting
- Permission checks
- Related data fetching
- Performance optimization

BDR provides the right structure from the start.
