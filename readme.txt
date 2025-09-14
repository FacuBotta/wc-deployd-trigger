=== Deploy Trigger for GitHub ===
Contributors: facudev
Tags: deploy, workflow, headless, actions, github
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Trigger GitHub Actions workflows from WordPress when content changes. Perfect for headless WordPress setups.

== Description ==

Easily trigger a GitHub Actions workflow from your WordPress site whenever a post is created, updated, or deleted. Perfect for headless WordPress setups to automate frontend deployments and keep your site in sync.

- Triggers a GitHub Actions workflow on post save or delete
- Manual deploy/reset options from the settings page
- Securely stores your GitHub token
- Works with any post type

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wc-github-deployer` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Create a GitHub Personal Access Token in your GitHub account with 'repo' permissions for the target repository.
4. Go to 'Settings' > 'GitHub Deploy' and configure your GitHub token, repository, workflow filename, and branch.
5. Save changes. The plugin will now trigger your workflow on post save or delete.

== Frequently Asked Questions ==

= Is my GitHub token safe? =
Yes, your token is stored securely in the WordPress options table and never displayed in plain text.

= Can I trigger the deploy manually? =
Yes, there is a button on the settings page to manually trigger the deploy.

= Does it work with custom post types? =
Yes, it works with any post type.

== Changelog ==

= 1.4 =
* Fixed WordPress.org compliance issues
* Updated text domain to match plugin slug (deploy-trigger-for-github)
* Renamed all functions and options with unique prefix (depltrfo_)
* Added external services documentation for GitHub API
* Improved code structure and naming conventions

= 1.3 =
* Improved error handling and logging
* Updated documentation

= 1.2 =
* Added reset button to clear plugin data
* Improved code structure and security

= 1.1 =
* Added support for post deletion trigger

= 1.0 =
* Initial release

== External services ==

This plugin connects to the GitHub API to trigger GitHub Actions workflows. This service is required to automatically deploy your site when content changes.

The plugin sends the following data to GitHub's API:
- Repository information (user/repo format)
- Workflow filename
- Branch reference (default: main)
- GitHub personal access token for authentication

This data is sent every time a post is created, updated, or deleted (when the post status is 'publish'). The GitHub token is stored securely in your WordPress database and is never displayed in plain text.

This service is provided by GitHub, Inc.:
- Terms of Service: https://docs.github.com/en/site-policy/github-terms/github-terms-of-service
- Privacy Policy: https://docs.github.com/en/site-policy/privacy-policies/github-privacy-statement

== Upgrade Notice ==

= 1.4 =
WordPress.org compliance update - fixes text domain, function naming, and adds external services documentation.

= 1.3 =
Recommended update from plugin check before submit the plugin on WordPress.org.