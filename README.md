# Krishna Template Server (KriTS)

A lightweight, flexible PHP framework for building web applications with template-based routing. KriTS makes it easy to organize your code by separating routes from templates while providing powerful pattern matching capabilities.

## Table of Contents

- [Introduction & Features](#introduction--features)
- [Installation & Setup](#installation--setup)
- [Project Structure](#project-structure)
- [Core Concepts](#core-concepts)
- [Usage Examples](#usage-examples)
- [Advanced Topics](#advanced-topics)
- [Development & Debug Mode](#development--debug-mode)
- [API Reference](#api-reference)
- [License & Contributing](#license--contributing)

---

## Introduction & Features

**KriTS** (Krishna Template Server) is designed for developers who want:
- **Lightweight routing**: No bloated dependencies or unnecessary features
- **Template separation**: Keep your logic clean and templates organized
- **Flexible patterns**: Match URLs with plain text, variables, and regex patterns
- **Easy configuration**: Minimal setup required to get started
- **Debug support**: Built-in error handling and debug mode for development

### Key Features:
- Simple route definition with `RouteNode` and `RouteExpression`
- URL pattern matching (literal, variables, regex, case-insensitive)
- Modular template system with pre/current/post hooks
- Automatic URL parameter extraction into `$path_vars`
- JSON request handling support
- Development mode for detailed error messages

---

## Installation & Setup

### Requirements
- PHP 8.1 or higher

### Install via Composer

```bash
composer require anshu-krishna/template-server
```

### Quick Start

Create a file `public/index.php`:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KriTS\Config;
use KriTS\Server;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

// Enable debug mode (optional, disable in production)
Config::$dev_mode = true;

// Initialize the server
Server::init();

// Define routes and execute
Server::execute(new RN(
    null,                           // pre: nothing before
    "@root",                        // here: main template
    null,                           // post: nothing after
    new RE('about', '@about'),      // /about -> about.php template
    new RE('404', '@404')           // 404 fallback
));
```

### Web Server Configuration

Configure your web server to route all requests through `index.php`:

**Apache (.htaccess)**:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?@_url_@=$1 [QSA,L]
</IfModule>
```

**Nginx**:
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?@_url_@=$1 last;
    }
}
```

---

## Project Structure

```
your-project/
├── public/
│   ├── index.php              # Entry point
│   └── ...other assets
├── src/
│   ├── templates/             # Template files
│   │   ├── root.php
│   │   ├── about.php
│   │   └── 404.html
│   ├── routes/                # Route definition files
│   │   └── ...
│   └── ...other code
├── vendor/                    # Composer dependencies
└── composer.json
```

**Default Paths:**
- `templates_path`: `{parent_dir}/src/templates/`
- `routes_path`: `{parent_dir}/src/routes/`

Override with `Server::init('/custom/templates/path', '/custom/routes/path')`

---

## Core Concepts

### 1. RouteNode

A `RouteNode` represents a point in your routing tree. It has three template hooks and can chain to other routes via `RouteExpression`.

```php
new RouteNode(
    $pre,    // Template BEFORE processing child routes
    $here,   // Template FOR this route segment
    $post,   // Template AFTER processing child routes
    ...$next // Child RouteExpression patterns
);
```

**Template Values:**
- `null`: No template
- `string` (plain text): Output directly as HTML
- `"@file.php"`: Load from `templates_path/file.php`
- `"#/absolute/path.php"`: Load from absolute path
- `array`: Multiple templates (all executed in order)

**Example:**
```php
new RouteNode(
    "@header",           // Show header first
    "Welcome!",          // Show welcome message
    "@footer",           // Show footer last
    new RE('about', '@about')  // Can go to /about
)
```

---

### 2. RouteExpression

A `RouteExpression` defines a URL pattern and what to do when it matches.

```php
new RouteExpression(
    $pattern,  // URL pattern (see formats below)
    $handler   // RouteNode or file path
);
```

**Pattern Formats:**

| Pattern | Behavior | Example |
|---------|----------|---------|
| `'about'` | Literal match | `/about` matches exactly |
| `'@name'` | Match anything, capture as variable | `/@name` matches `/john`, stores in `$path_vars['name']` |
| `'@id=[0-9]+'` | Regex match with capture | `/@id=[0-9]+` matches `/123`, stores in `$path_vars['id']` |
| `'@slug~[a-z-]+'` | Case-insensitive regex | `/@slug~[a-z-]+` matches `/Hello-World`, stores in `$path_vars['slug']` |

**Handler Formats:**

```php
// Option 1: Inline RouteNode
new RE('about', new RouteNode(
    null,
    '@about',
    null
))

// Option 2: Reference to template file
new RE('about', '@about')  // Loads from routes_path/about.php

// Option 3: Absolute path
new RE('about', '#/var/www/routes/about.php')
```

---

### 3. Config Class

Global configuration for the framework.

```php
use KriTS\Config;

Config::$dev_mode = true;  // Enable detailed error messages
```

When `$dev_mode = true`:
- Error messages are shown in HTML comments
- Debug output is visible
- Helpful for development

When `$dev_mode = false`:
- Error messages are hidden
- Safe for production

---

### 4. Server Class

Main entry point for the framework.

```php
use KriTS\Server;

// Initialize with default paths
Server::init();

// Or specify custom paths
Server::init('/path/to/templates', '/path/to/routes');

// Execute the routing
Server::execute($rootRouteNode);
```

**Key Methods:**
- `Server::init(?)`: Initialize framework
- `Server::execute(RouteNode)`: Process request and output
- `Server::echo_debug(mixed, bool)`: Output debug info (only in dev mode)
- `Server::echo_error(string, ?string)`: Output error info (only in dev mode)

---

### 5. Router Class

Handles URL parsing and query parameters.

```php
use KriTS\Router;

// Captured URL variables
$userId = Router::$path_vars['id'] ?? null;

// Query parameters and POST data
$_GET, $_POST  // Used by KriTS automatically
```

**Supported Data:**
- URL path variables: `/user/@id` → `Router::$path_vars['id']`
- Query strings: `/page?sort=name` → `$_GET['sort']`
- POST form data: `$_POST['field']`
- JSON requests: Automatically decoded and merged

---

## Usage Examples

### Example 1: Simple Blog

**Directory Structure:**
```
src/
├── templates/
│   ├── layout.php
│   ├── home.php
│   └── article.php
└── routes/
```

**public/index.php:**
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KriTS\Config;
use KriTS\Server;
use KriTS\Router;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

Config::$dev_mode = true;
Server::init();

Server::execute(new RN(
    '@layout',              // All pages start with layout
    '@home',                // Home page
    null,
    new RE('@slug=[a-z0-9-]+', new RN(
        null,
        '@article',         // Article template
        null
    )),
    new RE('404', '@404')
));
```

**templates/layout.php:**
```php
<!DOCTYPE html>
<html>
<head><title>My Blog</title></head>
<body>
    <nav><!-- Navigation --></nav>
    <!-- Content from child routes goes here -->
</body>
</html>
```

**templates/article.php:**
```php
<?php
$slug = \KriTS\Router::$path_vars['slug'] ?? 'unknown';
?>
<article>
    <h1><?= htmlspecialchars($slug) ?></h1>
    <p>Article content for: <?= htmlspecialchars($slug) ?></p>
</article>
```

### Example 2: API Endpoints with JSON

**public/index.php:**
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KriTS\Server;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

Server::init();

Server::execute(new RN(
    null,
    null,
    null,
    new RE('api', new RN(
        null,
        null,
        null,
        new RE('users', '@api/users'),
        new RE('posts', '@api/posts')
    )),
    new RE('404', '@404')
));
```

**src/routes/api/users.php:**
```php
<?php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$query = $_GET;

echo json_encode([
    'status' => 'ok',
    'method' => $method,
    'endpoint' => 'users',
    'query' => $query
]);
```

### Example 3: Dynamic Routes with Validation

**public/index.php:**
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KriTS\Server;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

Server::init();

Server::execute(new RN(
    '@header',
    '@home',
    '@footer',
    // Match /user/[number]
    new RE('@type=[a-z]+', new RN(
        null,
        '@dynamic',
        null,
        new RE('@id=[0-9]+', '@item')
    )),
    new RE('404', '@404')
));
```

### Example 4: Category & Product Routing

**public/index.php:**
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KriTS\Server;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

Server::init();

Server::execute(new RN(
    '@header',
    '@home',
    '@footer',
    new RE('@category', new RN(
        '@category-header',
        '@category-list',
        '@category-footer',
        new RE('@product', '@product-detail')
    )),
    new RE('404', '@404')
));
```

### Example 5: Admin Panel with Multiple Sections

**public/index.php:**
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use KriTS\Server;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

Server::init();

Server::execute(new RN(
    null,
    '@home',
    null,
    new RE('admin', new RN(
        '@admin/layout',
        '@admin/dashboard',
        null,
        new RE('users', '@admin/users'),
        new RE('settings', '@admin/settings'),
        new RE('logout', '@admin/logout')
    )),
    new RE('404', '@404')
));
```

---

## Advanced Topics

### Regex Pattern Matching

Use regex patterns to validate and capture URL segments:

```php
// Match any word (letters only)
new RE('@page=[a-zA-Z]+', '...')

// Match numbers with leading zeros
new RE('@code=[0-9]{4}', '...')

// Match domain-like slugs (letters, numbers, hyphens)
new RE('@slug=[a-z0-9-]+', '...')

// Match UUIDs
new RE('@id=[0-9a-f]{8}-[0-9a-f]{4}...', '...')

// Match emails
new RE('@email=[^@]+@[^@]+', '...')
```

All matched values are available in `Router::$path_vars`.

### Case-Insensitive Matching

Use `~` instead of `=` for case-insensitive regex:

```php
// Exact case: @name=[A-Z]+ matches "JOHN" only
new RE('@name=[A-Z]+', '...')

// Case-insensitive: @name~[a-z]+ matches "JOHN", "john", "John"
new RE('@name~[a-z]+', '...')
```

### Multiple Templates in Sequence

Execute multiple templates in order:

```php
new RouteNode(
    null,
    [
        "First template",
        "@template1",
        "@template2"
    ],
    null
)
```

All templates execute in order and output is concatenated.

### Accessing URL Variables in Templates

```php
<?php
use KriTS\Router;

$userId = Router::$path_vars['id'] ?? null;
$category = Router::$path_vars['category'] ?? null;

if ($userId) {
    echo "User: " . htmlspecialchars($userId);
}
?>
```

### Query Parameters and POST Data

```php
<?php
// GET parameters: /page?sort=name
$sort = $_GET['sort'] ?? 'default';

// POST parameters
$name = $_POST['name'] ?? '';

// JSON requests (auto-decoded)
$data = $_POST; // Contains decoded JSON if Content-Type is application/json
?>
```

---

## Development & Debug Mode

### Enable Debug Mode

In your entry point:

```php
use KriTS\Config;

Config::$dev_mode = true;  // Only in development!
```

### Using Debug Output

```php
use KriTS\Server;

// Output a debug message
Server::echo_debug("My variable", false);

// Output with print_r format
Server::echo_debug($array, true);
```

Debug messages appear as HTML comments in the response (visible in page source).

### Error Handling

Errors are automatically caught and displayed as HTML comments:

```php
// In dev mode:
<!-- Error: {"line":42, "file":"template.php", "msg":"Undefined variable"} -->

// In production mode:
<!-- Error: Message redacted -->
```

### Disable Under Development

When ready for production:
```php
Config::$dev_mode = false;
Server::init();
```

---

## API Reference

### Server Class

```php
namespace KriTS;

class Server {
    public static string $templates_path;
    public static string $routes_path;
    
    public static function init(
        ?string $templates_path = null,
        ?string $routes_path = null
    ): void;
    
    public static function execute(RouteNode $root): never;
    
    public static function echo_debug(
        mixed $value,
        bool $use_print_r = false
    ): void;
    
    public static function echo_error(
        string $msg,
        ?string $from = null
    ): void;
}
```

### Router Class

```php
namespace KriTS;

class Router {
    public static array $path_vars = [];
    
    public static function init(RouteNode $root): void;
    public static function execute(): bool;
}
```

### RouteNode Class

```php
namespace KriTS;

class RouteNode {
    public function __construct(
        array|string|null $pre,
        array|string|null $here,
        array|string|null $post,
        RouteExpression ...$next
    );
}
```

### RouteExpression Class

```php
namespace KriTS;

class RouteExpression {
    public function __construct(
        string $exp,
        RouteNode|string $route
    );
}
```

### Config Class

```php
namespace KriTS;

final class Config {
    public static bool $dev_mode = false;
}
```

---

## License & Contributing

### License

This project is licensed under the MIT License. See [LICENSE](LICENSE) file for details.

### Author

**Anshu Krishna**  
Email: anshu.krishna5@gmail.com

### Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -am 'Add your feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

### Support

For issues, questions, or suggestions, please open an issue on the project repository.

---

**Version**: 3.3.1  
**PHP Requirement**: 8.1+  
**Last Updated**: 2026