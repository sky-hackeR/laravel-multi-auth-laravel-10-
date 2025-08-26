# Laravel Multi Auth for Laravel 10

A **Laravel 10** compatible package for **multi-authentication** (e.g., Admin, Teacher, Student) with **custom scaffolding**, **password reset**, and **separate guard-based authentication**.

This package was **inspired by** and **credits** [AlAminFirdows/laravel-multi-auth](https://github.com/AlAminFirdows/laravel-multi-auth), rewritten and upgraded for Laravel 10 with a modern structure.

---

## ✅ Features
- Multi-auth for unlimited guards (Admin, Teacher, Student, etc.)
- Custom **login**, **register**, **logout** per guard
- Password reset support per guard
- Guard-specific middleware:
  - `RedirectIfGuard`
  - `RedirectIfNotGuard`
- Stubs for:
  - Controllers
  - Views
  - Migration
  - Models
  - Routes
- Uses **Bootstrap 5** for raw frontend scaffolding
- Artisan command to generate auth for a new guard
- Manual **vendor:publish** for stubs/config
- Laravel 10 **Service Provider & Auto-Discovery**

---

## 📂 Installation

### 1. Require the package via Composer
```bash
composer require skyhacker/laravel-multi-auth
2. Publish config and stubs
bash
Copy
Edit
php artisan vendor:publish --provider="SkyHackeR\MultiAuth\LaravelMultiAuthServiceProvider"
3. Run the installer command
bash
Copy
Edit
php artisan multiauth:install Admin
This will:
✔ Create Admin guard in config/auth.php
✔ Add Admin model, migration, routes, controllers, views

4. Run migrations
bash
Copy
Edit
php artisan migrate
📌 How to Create Another Guard
bash
Copy
Edit
php artisan multiauth:install Teacher
🛠 Default Structure After Installation
text
Copy
Edit
app/
 ├── Http/
 │    └── Controllers/
 │         └── Admin/...
resources/
 └── views/
      └── admin/
          └── auth/...
routes/
 └── admin.php
🔐 Password Reset
Separate reset for each guard

Guard-specific email templates under:

swift
Copy
Edit
resources/views/{guard}/auth/passwords
📜 Credits
Original work: AlAminFirdows

Laravel 10 upgrade & rewrite: SkyHackeR

