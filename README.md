# simple-prefix

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aslnbxrz/simple-prefix.svg)](https://packagist.org/packages/aslnbxrz/simple-prefix)
[![Total Downloads](https://img.shields.io/packagist/dt/aslnbxrz/simple-prefix.svg)](https://packagist.org/packages/aslnbxrz/simple-prefix)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Tiny, fast Eloquent prefix generator (trait + optional interface) with zero-hassle defaults.  
Designed for Laravel models where you need a consistent, readable, and configurable prefix (like `ORD-123-slug`) without boilerplate code.

---

## Features

- ⚡ **Lightweight & fast** – no `Collection`, no `data_get`, optimized for large datasets
- 🔧 **Configurable defaults** – via `config/simple-prefix.php`
- 📦 **Per-model constants** – `PREFIX`, `PREFIX_FROM`, `PREFIX_SEPARATOR`
- 🧩 **Dynamic override** – use `definePrefixVia()` or a runtime resolver
- ✅ **Safe by design** – prevents N+1 issues, includes caching
- 🛡️ **Zero overload** – works out of the box, but flexible if you need it

---

## Install

```bash
composer require aslnbxrz/simple-prefix
php artisan vendor:publish --tag=simple-prefix-config # optional