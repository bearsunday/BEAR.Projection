# BEAR.Projection

CQRS Query-side (Read Model) library for BEAR.Sunday

## Overview

BEAR.Projection provides a clean separation between commands (write) and queries (read) in BEAR.Sunday applications. It enables SQL-based projections that map directly to strongly-typed value objects via `query://` scheme.

## Installation

```bash
composer require bear/projection
```

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
-- var/sql/query/user.sql
SELECT id, name, email FROM users WHERE id = :id
```

### 4. Configure the Module

```php
use BEAR\Projection\Module\ProjectionModule;
use Ray\Di\AbstractModule;

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

### 5. Query via Resource

Access projections through the `query://` scheme:

```php
// Direct access
$ro = $this->resource->get('query://self/user', ['id' => $id]);
$user = $ro->body[0]; // UserProjection object
```

### 6. Embed in Resource

Use `#[Embed]` to embed query results in your resource:

```php
use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    #[Embed(rel: 'profile', src: 'query://self/user_profile{?id}')]
    public function onGet(string $id): static
    {
        // profile is automatically fetched and embedded
        return $this;
    }
}
```

The embedded query result contains `UserProfileProjection` objects:

```php
$user = $this->resource->get('app://self/user', ['id' => '123']);
$profile = $user->body['profile'][0]; // UserProfileProjection object
```

## File Structure

```text
var/
  sql/
    query/
      user.sql           # SQL query file
      user_profile.sql   # SQL query file (snake_case)
src/
  Projection/
    UserProjection.php   # Read model (value object)
    UserFactory.php      # Row-to-object converter
    UserProfileProjection.php
    UserProfileFactory.php
```

## Naming Conventions

| Component  | Naming Pattern        | Example                                           |
|------------|-----------------------|---------------------------------------------------|
| SQL file   | `{name}.sql`          | `user.sql`, `user_profile.sql`                    |
| Projection | `{Name}Projection`    | `UserProjection`, `UserProfileProjection`         |
| Factory    | `{Name}Factory`       | `UserFactory`, `UserProfileFactory`               |
| URI        | `query://self/{name}` | `query://self/user`, `query://self/user_profile`  |

Snake_case SQL file names are automatically converted to CamelCase class names:
- `user` -> `UserFactory`
- `user_profile` -> `UserProfileFactory`
- `admin_user_list` -> `AdminUserListFactory`
