# Krishna Template Server (KriTS)

KriTS is a light-weight framework for serving webpages based on templates.
___

***Under construction.*** Check later.

### Usage:
```php
<?php
use KriTS\Server;
use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

// Initilise the server
/*
Server::init(?string $templates_path = null, ?string $routes_path = null) : void;
*/
Server::init();

// Define the routes
/*
execute(RouteNode $root) : never
*/
Server::execute(new RN(
	"root_start",
	"root",
	"root_end",
	new RE('404', new RN(
		"404_start",
		"404",
		"404_end"
	)))
);
```
### RouteNode:
```php
// Signature:
new \KriTS\RouteNode (
	array|string|null $pre,
	array|string|null $here,
	array|string|null $post,
	RouteExpression ...$next
);
```
`$pre`, `$here` & `$post` are tempates and can have following values:
- **null**: It means there is no template
- **array**: One of more string values as described below
- **string**: It can be used in following ways
	- `Default`: plaintext as template.
	- `@file_path`: loads file at path `'file_path'` relative to the default `templates_path`
	- `#file_path`: loads file at absolute path `'file_path'`

### RouteExpression:
```php
// Signature:
new \KriTS\RouteExpression(
	string $exp,
	private RouteNode|string $route
);
```
`$route` can be a `RouteNode` object or a string containing path of route file.
If string starts with '@' then file_path is relative to the default routes_path.
If string starts with '#' then file_path is absolute.

`$exp` can have following formats:
- `'plaintext'`: matches `'plaintext'`
- `'@var1'`: matches anything. The matched value is assigned to `Router::$path_vars` list with `var1` as key.
- `'@var2=pattern'`: matches `RegExp` defined by `pattern`. The matched value is assigned to `Router::$path_vars` list with `var2` as key.
- `'@var3~pattern'`: matches `RegExp` defined by `pattern` with case-insensitive flag. The matched value is assigned to `Router::$path_vars` list with `var3` as key.