# AsapWo WordPress Plugin

AsapWo is a WordPress plugin that integrates SAP with WooCommerce, allowing for seamless management of product data and updates.

## Installation

1. Upload the `asapwo` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

1. Navigate to the 'AsapWo' menu in the WordPress admin dashboard.
2. Fill in the SAP Connection Settings, including CompanyDB, UserName, Server IP, Port, and Password.
3. Save the settings.


## Installation of automattic/woocommerce, the installation of the liberia is totally necessary.

```php
composer require automattic/woocommerce
```

## Usage

### SAP Connection Settings

To configure SAP connection settings, follow these steps:

1. Go to the 'AsapWo' menu in the WordPress admin dashboard.
2. Enter the required SAP connection details:
   - **CompanyDB**: Enter the SAP Company Database name.
   - **UserName**: Enter the SAP username.
   - **Server IP**: Enter the SAP server IP address.
   - **Port**: Enter the SAP server port.
   - **Password**: Enter the SAP password.
3. Click on the 'Save Settings' button.

### Update Products

To update products from SAP to WooCommerce:

1. Go to the 'AsapWo' menu in the WordPress admin dashboard.
2. Click on the 'Update Products' button.

## Database Setup

The plugin uses a database table to store SAP connection settings. The table is created automatically upon saving settings.

## SAP Integration Logic

The plugin performs the following SAP integration logic during product updates:

1. Establish a connection with the SAP server using provided credentials.
2. Retrieve product information from SAP based on SKU.
3. Update WooCommerce products with SAP data, including price and stock.
4. Log updates to a text file for reference.

## Notes

- The plugin uses the WooCommerce REST API for product updates.
- Ensure that the SAP server is accessible from the WordPress server.
- Verify the SAP and WooCommerce credentials are accurate for successful integration.

## Credits

- Plugin Name: AsapWo
- Description: SAP WooCommerce Integration By Jhon Corella
- Version: 1.0
- Author: CorellaInnovations

---

Feel free to customize the documentation according to your needs.
