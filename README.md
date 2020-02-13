# DesignInk Plugin Update Helper

This project is a Composer-distrubuted package designed to integrate a custom WordPress plugin project with the [DesignInk Plugin Update Server](https://github.com/designink-digital/designink-plugin-update-server). It provides the tools used to communicate with a plugin update server and perform information reporting and update installation.

## Installation

To install this package into your plugin project, simply navigate to the root directory of your project in a command terminal and run ``composer require designink/plugin-update-helper``. This installs the update helper into the vendor directoy and updates your Composer file.

## Usage

In order to have your WordPress plugin talk to an update server, you must define a plugin slug and a URL to access in your plugin.

#### This project assumes your plugin folder name will have the same name (slug) as the primary PHP file inside of it. That is, your plugin should have an effective ID of "plugin-slug/plugin-slug.php". If you do not organize your project as such, the update system will not work.

To tell the update helper what you plugin slug and update URL are, you simply need to require the update helper, then add the slug and URL to the plugin update list via a key/value pair.

```php
/* Inside of "plugin-slug/plugin-slug.php" */

use Designink\WordPress\Plugin_Update_Helper\v1_0_0\Plugin_Helper_Update_List;

// Include the plugin update helper
require_once __DIR__ . '/vendor/designink/plugin-update-helper/index.php';

// Add the slug and URL to the list
Plugin_Helper_Update_List::add_plugin( 'plugin-slug', 'https://designinkdigital.com/' );
```
If you plan on using a private GitHub repository, you should provide an SSL key to use to encrypt your API token in "Settings > DesignInk Settings". This token must match the token on the update server.
