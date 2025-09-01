# Laravel Multi-Auth (Laravel 10+)

A simple and flexible multi-authentication scaffolder for Laravel 10+, forked and upgraded from [alaminfirdows/laravel-multi-auth](https://github.com/alaminfirdows/laravel-multi-auth).  

This package helps you quickly set up **multiple authentication guards** (e.g., `Admin`, `Teacher`, `Manager`) with dedicated models, migrations, controllers, and route groups.

---

## ✨ Features

- Scaffold multiple guards in minutes.  
- Auto-generates:
  - Model (`App\Models\{Guard}`)
  - Migration
  - Routes (`routes/{guard}.php`)
  - Controllers  
- Updates `config/auth.php` automatically.  
- Ships with a simple artisan command.  
- Compatible with Laravel 10+.

---

## ⚙️ Installation

Install via composer:

```bash
composer require skyhacker/laravel-multi-auth
```

---

## 📦 Publish Config

Publish the package config:

```bash
php artisan vendor:publish --tag=multi-auth-config
```

This will create:

```
config/multiauth.php
```

---

## 🚀 Usage

Scaffold a new guard (e.g., **Admin**):

```bash
php artisan multiauth:install Admin
```

This will:  
- Add `Admin` guard to `config/auth.php`  
- Generate `App\Models\Admin`  
- Generate migration for `admins` table  
- Create routes file `routes/admin.php`  
- Create `Admin` controllers  

Finally, run migrations:

```bash
php artisan migrate
```

---

## ⚡ Example

```bash
php artisan multiauth:install Teacher
```

- Model: `App\Models\Teacher`
- Migration: `create_teachers_table`
- Route file: `routes/teacher.php`
- Controller: `App\Http\Controllers\Teacher\Auth\LoginController`, etc.

---

## 🔧 Configuration

You can define default guards in **`config/multiauth.php`**:

```php
'guards' => [
    'admin' => App\Models\Admin::class,
    'teacher' => App\Models\Teacher::class,
],
```

Running `php artisan multiauth:install` without args will scaffold these.

---

## 🛠 Development Notes

- Only `config/multiauth.php` is published by default.  
- All scaffolding (models, routes, controllers, etc.) is handled by the `multiauth:install` command.  
- Keeps your app clean and avoids dumping unnecessary stubs.  

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you’d like to change.  

