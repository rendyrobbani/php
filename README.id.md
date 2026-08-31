# RendyRobbani PHP

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php\&logoColor=white)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-Required-885630?logo=composer\&logoColor=white)](https://getcomposer.org/)

Library/framework PHP ringan yang berfokus pada **component berbasis attribute, dependency injection, metadata entity, object mapping, dan pembuatan implementasi repository secara otomatis**.

Project ini dibangun dengan konsep sederhana:

> **Definisikan component, entity, dan repository — biarkan framework menangani infrastrukturnya.**

---

## ✨ Fitur

* 🧩 **Arsitektur berbasis component**
* 💉 **Dependency injection**
* 🏷️ **Konfigurasi menggunakan PHP Attribute**
* 🗃️ **Manajemen metadata entity**
* 🔄 **Entity mapping**
* 📦 **Repository abstraction**
* ⚡ **Pembuatan implementasi repository secara otomatis**
* 🧠 **Pemrosesan runtime berbasis Reflection**
* 🧪 **Automated test suite**
* 🛠️ **Manajemen package menggunakan Composer**

---

## 📦 Persyaratan

* PHP **8.3 atau lebih baru**
* Composer
* Koneksi database yang didukung untuk fitur persistence

---

## 🚀 Instalasi

Install package menggunakan Composer:

```bash
composer require rendyrobbani/php
```

Kemudian load autoloader Composer:

```php
require_once __DIR__ . "/vendor/autoload.php";
```

---

## 🧩 Component

Class yang ingin dikelola oleh framework dapat didaftarkan menggunakan attribute `#[Component]`.

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

Component kemudian dapat diambil melalui application:

```php
$userService = Application::getComponent(
    UserService::class
);
```

Component juga dapat menggunakan dependency injection melalui constructor.

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

Framework akan melakukan resolusi terhadap dependency yang dibutuhkan secara otomatis.

---

## 🗃️ Entity

Entity didefinisikan menggunakan PHP Attribute.

Contoh:

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

Metadata entity diproses menggunakan Reflection dan direpresentasikan melalui object metadata seperti:

* `EntityInfo`
* `FieldInfo`
* `RepositoryInfo`

Dengan pendekatan ini, persistence layer dapat bekerja berdasarkan definisi entity tanpa membutuhkan konfigurasi yang berulang.

---

## 📚 Repository

Repository menyediakan abstraction untuk mengakses entity yang tersimpan di database.

Repository dapat didefinisikan sebagai interface:

```php
#[Component]
#[Repository(entity: User::class)]
interface UserRepository
{
    public function findAll(): array;

    public function findById(int $id): ?User;
}
```

Untuk behavior repository dasar, developer tidak perlu membuat implementation secara manual.

---

## ⚡ Automatic Repository Implementation

Salah satu fitur utama framework adalah kemampuan untuk membuat implementation repository secara otomatis.

Ketika aplikasi meminta sebuah repository interface:

```php
$repository = Application::getComponent(
    UserRepository::class
);
```

framework akan memeriksa apakah:

```text
UserRepositoryImpl
```

sudah tersedia.

### Jika sudah tersedia

Implementation yang sudah ada akan digunakan.

### Jika belum tersedia

Jika interface memiliki attribute `#[Repository]`, framework akan membuat implementation secara otomatis.

Secara konsep prosesnya:

```text
UserRepository
       │
       ▼
ComponentFactory
       │
       ├── Apakah UserRepositoryImpl tersedia?
       │        │
       │        ├── Ya → Gunakan implementation yang ada
       │        │
       │        └── Tidak
       │             │
       │             ▼
       │        Terdapat @Repository
       │             │
       │             ▼
       │        RepositoryImplCode
       │             │
       │             ▼
       │        Generate implementation
       │             │
       │             ▼
       │        Buat instance
       │
       ▼
UserRepository
```

### Konvensi Penamaan

Implementation yang dibuat secara otomatis mengikuti pola:

```text
<RepositoryInterface>Impl
```

Contoh:

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

Dengan demikian, implementation yang dihasilkan tetap memiliki hubungan yang jelas dengan repository interface-nya.

---

## 🔧 Custom Repository Implementation

Pembuatan implementation secara otomatis tidak menghilangkan kemampuan untuk membuat implementation sendiri.

Jika developer menyediakan:

```php
final class UserRepositoryImpl implements UserRepository
{
    // Custom implementation
}
```

framework dapat menggunakan implementation tersebut daripada membuat implementation baru.

Dengan demikian terdapat dua pilihan:

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
 Dibuat developer
```

Repository sederhana dapat menggunakan automatic implementation, sementara repository dengan kebutuhan khusus tetap dapat menggunakan implementation manual.

---

## 🔄 Entity Mapping

Framework menyediakan entity mapping layer untuk mengubah hasil persistence menjadi object entity.

Arsitektur persistence dipisahkan menjadi beberapa tanggung jawab:

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

Pemisahan ini menjaga agar metadata entity, mapping, dan repository tetap memiliki tanggung jawab yang terpisah.

---

## 🧠 Arsitektur Berbasis Reflection

Framework banyak menggunakan PHP Reflection.

Reflection digunakan untuk membaca:

* Class
* Interface
* Attribute
* Constructor dependency
* Field entity
* Repository method
* Parameter method
* Return type

Informasi tersebut kemudian digunakan oleh component system, persistence system, dan code-generation system.

---

## 🧪 Testing

Project memiliki test suite khusus di:

```text
src/test/
```

Test mencakup berbagai bagian framework seperti:

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

Repository juga memiliki test untuk runtime repository generation.

Salah satu behavior yang diuji adalah kemampuan framework untuk membuat implementation repository meskipun class implementation tersebut belum tersedia sebelumnya.

---

## 🏗️ Struktur Project

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

### Modul Utama

| Modul           | Tanggung Jawab                              |
|-----------------|---------------------------------------------|
| `Application`   | Registry aplikasi dan akses component       |
| `Component`     | Resolusi component dan dependency injection |
| `Configuration` | Konfigurasi aplikasi                        |
| `Connection`    | Infrastruktur koneksi database              |
| `Persistence`   | Infrastruktur entity dan repository         |
| `Code`          | Pembuatan source code saat runtime          |
| `File`          | Utility yang berkaitan dengan file          |
| `Exception`     | Exception khusus framework                  |

---

## 🛣️ Roadmap

Project masih terus dikembangkan.

Beberapa kemungkinan pengembangan berikutnya:

* Kemampuan query repository yang lebih luas
* Query parser yang lebih baik
* Pagination
* Transaction management
* Fitur persistence tambahan
* Caching untuk runtime code generation
* Integration test yang lebih lengkap
* Dukungan beberapa database dialect
* Lifecycle application/container yang lebih baik

Daftar di atas merupakan kemungkinan pengembangan dan **belum tentu tersedia pada versi saat ini**.

---

## 📌 Status Project

Project ini masih dalam tahap pengembangan aktif.

Arsitektur dan API dapat berubah seiring perkembangan framework.

Untuk penggunaan production, evaluasi terlebih dahulu implementasi dan test coverage yang tersedia sesuai kebutuhan aplikasi.

---

## 📄 License

Lihat informasi license pada repository untuk ketentuan penggunaan yang berlaku.

---

## 🌐 Bahasa

* **English** — [`README.md`](README.md)
* **Bahasa Indonesia** — `README.id.md`
