# BEAR.Projection

Read-optimized query library for BEAR.Resource `#[Embed]`

## Overview

BEAR.Projection implements the [BDR Pattern](docs/BDR_PATTERN.md) for the Read side, providing `query://` scheme resources designed for `#[Embed]`. A companion to [Ray.MediaQuery](https://github.com/ray-di/Ray.MediaQuery).

### Features

- **Embed-first design** - Optimized for `#[Embed]` in ResourceObject
- **Parallel SQL execution** - Multiple `#[Embed]` queries run concurrently
- **DI-powered Factory** - Inject dependencies for data transformation

## Installation

```bash
composer require bear/projection
```

## Usage

### 1. Define a Projection

```php
namespace MyApp\Projection;

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

### 2. Create a Factory with DI

Factory can inject dependencies for data transformation and validation:

```php
namespace MyApp\Projection;

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

### 3. Write the SQL Query

```sql
-- var/sql/query/user_profile.sql
SELECT id, name, birth_date, avatar_path FROM users WHERE id = :id
```

### 4. Configure the Module

```php
use BEAR\Projection\Module\ProjectionModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ProjectionModule(
            sqlDir: __DIR__ . '/../var/sql/query',
            projectionNamespace: 'MyApp\\Projection\\',
        ));
    }
}
```

### 5. Embed in Resource

```php
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    #[Embed(rel: 'profile', src: 'query://self/user_profile{?id}')]
    #[Embed(rel: 'orders', src: 'query://self/user_orders{?id}')]
    public function onGet(string $id): static
    {
        // Both queries execute in parallel
        return $this;
    }
}
```

## File Structure

```text
var/sql/query/
  user_profile.sql
  user_orders.sql

src/Projection/
  UserProfile.php
  UserProfileFactory.php
  UserOrders.php
  UserOrdersFactory.php
```

## Naming Conventions

| SQL file           | Factory class        | URI                            |
|--------------------|----------------------|--------------------------------|
| `user_profile.sql` | `UserProfileFactory` | `query://self/user_profile`    |
| `user_orders.sql`  | `UserOrdersFactory`  | `query://self/user_orders`     |
