# Nezuko CRM Commands & Coding Standards

## Commands
- **Development**: `composer dev` (serves app, queue listener, logs, vite)
- **Build**: `npm run build`
- **Code styling**: `./vendor/bin/pint` (Laravel Pint)
- **Testing**: `./vendor/bin/pest` (all tests)
- **Single test**: `./vendor/bin/pest tests/path/to/TestFile.php` or `./vendor/bin/pest tests/path/to/TestFile.php::testName`
- **Migrations**: `php artisan migrate`
- **Generate resource**: `php artisan make:filament-resource ModelName`

## Coding Standards
- **PHP Version**: 8.2+
- **Namespaces**: Follow PSR-4 (App\\, Database\\Factories\\, Database\\Seeders\\, Tests\\)
- **Docblocks**: Document properties, methods and return types with PHPDoc
- **Types**: Use strict typing and return type declarations
- **Naming**: PascalCase for classes, camelCase for variables/methods
- **Style**: Laravel Pint (PSR-12 based) for formatting
- **Testing**: Use Pest for testing with feature and unit tests
- **Filament**: Follow Filament conventions for resources, forms and tables