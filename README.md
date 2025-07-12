# Deploy Trigger for GitHub

[![Contributors](https://img.shields.io/badge/Contributors-facudev-blue.svg)](https://github.com/facudev)
[![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)](https://wordpress.org)
[![License](https://img.shields.io/badge/License-GPLv2-yellow.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Trigger GitHub Actions workflows from WordPress when content changes. Perfect for headless WordPress setups.

## Description

Easily trigger a GitHub Actions workflow from your WordPress site whenever a post is created, updated, or deleted. Perfect for headless WordPress setups to automate frontend deployments and keep your site in sync.

### Features
- Triggers a GitHub Actions workflow on post save or delete
- Manual deploy/reset options from the settings page
- Securely stores your GitHub token
- Works with any post type

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wc-github-deployer` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to 'Settings' > 'GitHub Deploy' and configure your GitHub token, repository, workflow filename, and branch.
4. Save changes. The plugin will now trigger your workflow on post save or delete.

## FAQ

### Is my GitHub token safe?
Yes, your token is stored securely in the WordPress options table and never displayed in plain text.

### Can I trigger the deploy manually?
Yes, there is a button on the settings page to manually trigger the deploy.

### Does it work with custom post types?
Yes, it works with any post type.

## Changelog

### 1.3
* Improved error handling and logging
* Updated documentation

### 1.2
* Added reset button to clear plugin data
* Improved code structure and security

### 1.1
* Added support for post deletion trigger

### 1.0
* Initial release

## Upgrade Notice

### 1.3
Recommended update from plugin check before submit the plugin on WordPress.org.