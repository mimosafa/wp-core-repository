# mimosafa/wp-core-repository

WordPress post type, taxonomy registration helper.

## Installation

Create a composer.json in your plugin root or mu-plugins.
```
{
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/mimosafa/wp-core-repository.git"
		}
	],
    "require": {
        "mimosafa/wp-core-repository": "dev-master"
    }
}
```

Place the following code into your plugin.

```
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
```

Then:

```
$ composer install
```

## How to use

```
use mimosafa\WP\CoreRepository as cr;
$factory = new cr\Factory();
$product = $factory->create_post_type( 'product', 'public=1&has_archive=1' );
```
