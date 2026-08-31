# Rendy Robbani PHP

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php\&logoColor=white)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-Required-885630?logo=composer\&logoColor=white)](https://getcomposer.org/)

A lightweight PHP framework/library focused on **attribute-based components, dependency injection, entity metadata, object mapping, and automatic repository implementation generation**.

The project is designed around a simple idea:

> **Define your components, entities, and repositories — let the framework handle the infrastructure.**

---

## ✨ Features

* 🧩 **Component-based architecture**
* 💉 **Dependency injection**
* 🏷️ **PHP Attribute-based configuration**
* 🗃️ **Entity metadata management**
* 🔄 **Entity mapping**
* 📦 **Repository abstraction**
* ⚡ **Automatic repository implementation generation**
* 🧠 **Reflection-based runtime processing**
* 🧪 **Automated test suite**
* 🛠️ **Composer-based package management**

---

## 📦 Requirements

* PHP **8.3 or higher**
* Composer
* A supported database connection for persistence features

---

## 🚀 Installation

Install the package using Composer:

```bash
composer require rendyrobbani/php
```

Then load Composer's autoloader:

```php
require_once __DIR__ . "/vendor/autoload.php";
```

---

## 🧩 Components

Classes that should be managed by the framework can be registered using the `#[Component]` attribute.

```php
use RendyRobbani\PHP\Component\Component;

#[Component]
final class UserService
{
    public function getUsers(): array
    {
        return [];
    }
}
```

The component can then be resolved through the application:

```php
$userService = Application::getComponent(
    UserService::class
);
```

Components can also participate in dependency injection through their constructors.

```php
#[Component]
final class UserController
{
    public function __construct(
        private UserService $userService
    ) {
    }
}
```

The framework resolves the required component automatically.

---

## 🗃️ Entities

Entities are defined using PHP Attributes.

A typical entity can be declared as:

```php
#[Entity(table: "users")]
final class User
{
    #[Id]
    #[Column]
    public ?int $id = null;

    #[Column]
    public string $name;

    #[Column]
    public string $email;
}
```

Entity metadata is processed through reflection and represented internally by metadata objects such as:

* `EntityInfo`
* `FieldInfo`
* `RepositoryInfo`

This allows the persistence layer to work with entity definitions without requiring repetitive configuration.

---

## 📚 Repositories

Repositories provide an abstraction for accessing persistent entities.

A repository can be declared as an interface:

```php
#[Component]
#[Repository(entity: User::class)]
interface UserRepository
{
    public function findAll(): array;

    public function findById(int $id): ?User;
}
```

No implementation class is required for the basic repository behavior.

---

## ⚡ Automatic Repository Implementation

One of the core features of the framework is the ability to generate a repository implementation automatically.

When the application requests a repository interface:

```php
$repository = Application::getComponent(
    UserRepository::class
);
```

the framework checks whether:

```text
UserRepositoryImpl
```

already exists.

### If it exists

The existing implementation is used.

### If it does not exist

If the interface has the `#[Repository]` attribute, the framework generates the implementation automatically.

The process is conceptually:

```text
UserRepository
       │
       ▼
ComponentFactory
       │
       ├── UserRepositoryImpl exists?
       │        │
       │        ├── Yes → Use existing implementation
       │        │
       │        └── No
       │             │
       │             ▼
       │        @Repository detected
       │             │
       │             ▼
       │        RepositoryImplCode
       │             │
       │             ▼
       │        Generate implementation
       │             │
       │             ▼
       │        Create instance
       │
       ▼
UserRepository
```

### Naming Convention

Generated implementations follow:

```text
<RepositoryInterface>Impl
```

For example:

```text
UserRepository
      ↓
UserRepositoryImpl
```

```text
AccountRepository
      ↓
AccountRepositoryImpl
```

This keeps the generated implementation directly associated with its repository interface.

---

## 🔧 Custom Repository Implementation

Automatic generation does not prevent custom implementations.

If you provide:

```php
final class UserRepositoryImpl implements UserRepository
{
    // Custom implementation
}
```

the framework can use the existing implementation instead of generating one.

This gives you two options:

### Automatic

```text
UserRepository
      ↓
UserRepositoryImpl
      ↑
   Generated
```

### Custom

```text
UserRepository
      ↓
UserRepositoryImpl
      ↑
 Developer-defined
```

This allows simple repositories to remain lightweight while still supporting custom behavior when needed.

---

## 🔄 Entity Mapping

The framework provides an entity mapping layer for converting persistence results into entity objects.

The persistence architecture is separated into several responsibilities:

```text
Entity
  │
  ▼
EntityInfo
  │
  ▼
EntityMapper
  │
  ▼
Entity Object
```

This separation keeps entity metadata, mapping, and repository responsibilities independent.

---

## 🧠 Reflection-Based Architecture

The framework makes extensive use of PHP Reflection.

Reflection is used to inspect:

* Classes
* Interfaces
* Attributes
* Constructor dependencies
* Entity fields
* Repository methods
* Method parameters
* Return types

The collected information is then used by the framework's component, persistence, and code-generation systems.

---

## 🧪 Testing

The project includes a dedicated test suite under:

```text
src/test/
```

Tests cover important framework components such as:

```text
Application
ComponentFactory
EntityInfoFactory
EntityMapper
RepositoryCode
RepositoryImplCode
Connection
File
```

The repository also tests runtime repository generation.

For example, the framework verifies that a repository implementation can be created even when the implementation class does not initially exist.

---

## 🏗️ Project Structure

```text
src/
├── main/
│   ├── Application.php
│   ├── Code/
│   ├── Component/
│   ├── Configuration/
│   ├── Connection/
│   ├── Exception/
│   ├── File/
│   └── Persistence/
│
└── test/
    ├── Code/
    ├── Component/
    ├── Connection/
    ├── File/
    ├── Persistence/
    └── ApplicationTest.php
```

### Main Modules

| Module          | Responsibility                                   |
|-----------------|--------------------------------------------------|
| `Application`   | Application-level registry and component access  |
| `Component`     | Component resolution and dependency injection    |
| `Configuration` | Application configuration                        |
| `Connection`    | Database connection infrastructure               |
| `Persistence`   | Entity and repository persistence infrastructure |
| `Code`          | Runtime source-code generation                   |
| `File`          | File-related utilities                           |
| `Exception`     | Framework-specific exceptions                    |

---

## 🛣️ Roadmap

The project is actively evolving.

Potential future improvements include:

* More repository query capabilities
* Improved query parsing
* Pagination support
* Transaction management
* Additional persistence features
* Better runtime code-generation caching
* More comprehensive integration tests
* Multiple database dialect support
* Improved application/container lifecycle

These items represent possible future directions and are **not necessarily available in the current release**.

---

## 📌 Project Status

This project is currently under active development.

The architecture and APIs may change as the framework evolves.

For production usage, carefully evaluate the current implementation and test coverage for your specific requirements.

---

## 📄 License

See the repository's license information for the applicable terms.

---

## 🌐 Language

* **English** — `README.md`
* **Bahasa Indonesia** — [`README.id.md`](README.id.md)
