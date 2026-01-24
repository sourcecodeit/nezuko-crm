# Security Hardening Guide - Nezuko CRM

## 🔒 Package Update Status
**Date**: January 24, 2026  
**Status**: ✅ All packages updated to latest secure versions  
**Vulnerabilities**: ✅ No known security vulnerabilities found

### Key Updates
- **Laravel Framework**: 11.31 → 11.48.0
- **Livewire**: 3.5.12 → 3.7.6 (critical security patches)
- **Filament**: 3.2.127 → 3.3.47
- **Symfony Components**: Updated to latest patch versions

---

## 🚨 Critical Security Configurations

### 1. Environment Security
Ensure your `.env` file in production has:

```env
APP_DEBUG=false                    # MUST be false in production
APP_ENV=production                 # Set to production
SESSION_ENCRYPT=true               # Enable session encryption
BCRYPT_ROUNDS=12                   # Strong password hashing
```

### 2. Livewire-Specific Security
Recent Livewire vulnerabilities exploit:
- **Unpublished property access**
- **Method invocation attacks**
- **Data binding manipulation**

#### Mitigation:
```php
// app/Livewire/YourComponent.php
#[Livewire]
class YourComponent extends Component
{
    // Mark protected properties explicitly
    #[Computed]
    public function items()
    {
        return auth()->user()->items; // Always check auth
    }
    
    // Validate all public methods
    #[Validate('required|string')]
    public string $input = '';
    
    public function updateData()
    {
        // Validate BEFORE processing
        $this->validate();
        // Process safely
    }
}
```

### 3. Laravel-Specific Security

#### A. CSRF Protection (Enabled by Default)
```blade
<!-- Always include in forms -->
@csrf

<!-- Or in AJAX requests -->
<script>
  headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
</script>
```

#### B. Input Validation
```php
// Always validate user input
$validated = $request->validate([
    'email' => 'required|email|max:255',
    'name' => 'required|string|max:255',
    // ... other fields
]);
```

#### C. Query Injection Prevention
```php
// ✅ GOOD - Uses parameter binding
User::where('email', $userInput)->first();
DB::table('users')->where('email', '=', $userInput)->get();

// ❌ BAD - Vulnerable to injection
DB::raw("SELECT * FROM users WHERE email = '$userInput'");
```

#### D. Mass Assignment Protection
```php
// app/Models/User.php
protected $fillable = ['name', 'email']; // Only these can be mass-assigned
// OR
protected $guarded = []; // Explicitly guard all, then use $fillable

// Usage
$user = User::create($request->only('name', 'email')); // Safe
```

---

## 🔐 Regular Maintenance

### Weekly Security Checks
```bash
# Check for new vulnerabilities in dependencies
composer audit

# Run security-focused tests
./vendor/bin/pest tests/ --filter Security

# Check for known CVEs in installed packages
composer audit --locked
```

### Monthly Updates
```bash
# Check for outdated packages
composer show --outdated

# Update to latest patch versions (minor version bumps too)
composer update
```

### Quarterly Major Reviews
```bash
# Check Laravel security advisories
# https://www.laravel.com/security

# Review Filament security updates
# https://filamentphp.com/docs/support

# Check Livewire security notices
# https://livewire.laravel.com/docs/security
```

---

## 🛡️ Additional Hardening Steps

### 1. HTTP Headers Security
Add to `app/Http/Middleware/` or configure in web server:

```php
// app/Http/Middleware/SecurityHeaders.php
header('X-Content-Type-Options: nosniff');           // Prevent MIME sniffing
header('X-Frame-Options: DENY');                      // Prevent clickjacking
header('X-XSS-Protection: 1; mode=block');           // XSS protection
header('Strict-Transport-Security: max-age=31536000'); // Force HTTPS
header('Content-Security-Policy: default-src self'); // CSP policy
```

### 2. Rate Limiting
```php
// routes/web.php
Route::middleware('throttle:60,1')->group(function () {
    // Protected routes
});
```

### 3. Authentication Hardening
```php
// config/auth.php
'passwords' => [
    'users' => [
        'algorithm' => 'bcrypt',      // Use bcrypt, not deprecated algos
        'rounds' => 12,                // Increase rounds for slower hashing
        'expire' => 60,                // Password reset link expiry
        'throttle' => 5,               // Rate limit password reset attempts
    ],
],
```

### 4. CORS Security (if using API)
```php
// config/cors.php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => env('CORS_ALLOWED_ORIGINS', ''), // Whitelist specific origins
'supports_credentials' => true,
```

---

## 🔍 Common Attack Vectors & Prevention

### SQL Injection
```php
// ❌ Vulnerable
$users = DB::select("SELECT * FROM users WHERE id = $id");

// ✅ Safe
$users = DB::select("SELECT * FROM users WHERE id = ?", [$id]);
$users = User::where('id', $id)->get();
```

### Cross-Site Scripting (XSS)
```blade
<!-- ❌ Vulnerable -->
{!! $user->bio !!}

<!-- ✅ Safe -->
{{ $user->bio }}

<!-- For actual HTML content, use Purify -->
{!! Purify::clean($htmlContent) !!}
```

### Cross-Site Request Forgery (CSRF)
```blade
<!-- ❌ Vulnerable - no CSRF token -->
<form method="POST" action="/delete-user">
    <!-- ... -->
</form>

<!-- ✅ Safe - includes CSRF token -->
<form method="POST" action="/delete-user">
    @csrf
    <!-- ... -->
</form>
```

### Insecure Direct Object Reference (IDOR)
```php
// ❌ Vulnerable - no auth check
$invoice = Invoice::find($id);

// ✅ Safe - verify ownership
$invoice = auth()->user()->invoices()->findOrFail($id);
```

---

## 📋 Post-Compromise Checklist

Since your site was compromised, complete these steps:

- [ ] **Change all credentials**
  - Database passwords
  - API keys (OpenAI, etc.)
  - Session encryption keys (`APP_KEY`)
  - Admin user passwords

- [ ] **Review logs**
  ```bash
  tail -f storage/logs/laravel.log
  php artisan pail  # Real-time log viewer
  ```

- [ ] **Check for backdoors**
  - Review `app/` directory for unauthorized files
  - Check `.env` for unusual entries
  - Verify `composer.json` dependencies

- [ ] **Regenerate APP_KEY**
  ```bash
  php artisan key:generate
  ```

- [ ] **Clear caches**
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```

- [ ] **Run all tests**
  ```bash
  ./vendor/bin/pest
  ```

- [ ] **Deploy updates**
  ```bash
  git push origin main
  composer update  # On production server
  php artisan migrate
  php artisan optimize
  ```

---

## 📚 Resources

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Livewire Security](https://livewire.laravel.com/docs/security)
- [Filament Security](https://filamentphp.com/docs/support)
- [PHP Security](https://www.php.net/manual/en/security.php)

---

## 🔄 Monitoring & Alerts

### Recommended Tools
- **Composer Audit**: Built-in vulnerability scanning
- **Laravel Telescope**: Debug & monitor requests (dev only)
- **Sentry**: Error tracking & monitoring
- **Scout**: APM monitoring
- **Cloudflare**: DDoS protection & WAF

---

**Last Updated**: January 24, 2026  
**Next Review**: April 24, 2026 (Quarterly)
