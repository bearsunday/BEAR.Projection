# BEAR.Projection

CQRS Query-side (Read Model) library for BEAR.Sunday

## Overview

BEAR.Projection provides a clean separation between commands (write) and queries (read) in BEAR.Sunday applications. It enables SQL-based projections that map directly to strongly-typed value objects.

## Installation

```bash
composer require bear/projection
```

## Requirements

- PHP 8.1+
- ext-mysqli
- bear/resource ^1.0
- ray/di ^2.0

## Usage

### 1. Define a Projection (Read Model)

```php
namespace MyApp\Projection;

final class UserProjection
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}
```

### 2. Create a Factory

The factory converts SQL row data to your Projection object:

```php
namespace MyApp\Projection;

final class UserFactory
{
    public function __invoke(string $id, string $name, string $email): UserProjection
    {
        return new UserProjection($id, $name, $email);
    }
}
```

### 3. Write the SQL Query

Create a SQL file with named placeholders (`:name` style):

```sql
-- Projection/user.sql
SELECT id, name, email FROM users WHERE id = :id
```

### 4. Configure the Module

```php
use BEAR\Projection\Module\QueryModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new QueryModule(__DIR__ . '/Projection'));
    }
}
```

### 5. Query via Resource

Access projections through the `query://` scheme:

```php
use BEAR\Resource\ResourceInterface;

class UserController
{
    public function __construct(
        private ResourceInterface $resource,
    ) {
    }

    public function show(string $id): UserProjection
    {
        $ro = $this->resource->get('query://self/user', ['id' => $id]);

        return $ro->body[0]; // UserProjection object
    }
}
```

## File Structure

```
src/
  Projection/
    user.sql           # SQL query file
    UserProjection.php # Read model (value object)
    UserFactory.php    # Row-to-object converter
```

## Naming Conventions

| Component | Naming Pattern | Example |
|-----------|---------------|---------|
| SQL file | `{name}.sql` | `user.sql` |
| Projection | `{Name}Projection` | `UserProjection` |
| Factory | `{Name}Factory` | `UserFactory` |
| URI | `query://self/{name}` | `query://self/user` |

## Commands

```bash
composer test      # Run unit tests
composer tests     # Run tests and quality checks
composer cs-fix    # Fix coding style
composer sa        # Run static analysis
```
