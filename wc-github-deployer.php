<?php

/**
 * Plugin Name: GitHub Deploy Trigger
 * Description: Trigger GitHub Actions workflow from WordPress when a post is saved or deleted.
 * Version: 1.2
 * Author: facudev
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-github-deployer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Register plugin settings
 * 
 * Sets up the options needed for GitHub integration and registers them
 * with WordPress settings API for proper sanitization and handling.
 */
function wc_github_deployer_register_settings()
{
  add_option('wc_github_deployer_token', '');
  add_option('wc_github_deployer_repo', '');
  add_option('wc_github_deployer_workflow', '');
  add_option('wc_github_deployer_ref', 'main');

  register_setting('wc_github_deployer_options', 'wc_github_deployer_token', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  register_setting('wc_github_deployer_options', 'wc_github_deployer_repo', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  register_setting('wc_github_deployer_options', 'wc_github_deployer_workflow', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  register_setting('wc_github_deployer_options', 'wc_github_deployer_ref', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
}
add_action('admin_init', 'wc_github_deployer_register_settings');

/**
 * Add settings page to WordPress admin
 * 
 * Creates a new menu item under Settings for configuring the GitHub deployment options.
 */
function wc_github_deployer_menu()
{
  add_options_page(
    'GitHub Deploy Settings',
    'GitHub Deploy',
    'manage_options',
    'wc-github-deployer',
    'wc_github_deployer_options_page'
  );
}
add_action('admin_menu', 'wc_github_deployer_menu');

/**
 * Render the settings page HTML
 * 
 * Displays the form for configuring GitHub integration settings
 * and handles the reset functionality for plugin data.
 */
function wc_github_deployer_options_page()
{
  // Process reset if form was submitted
  if (
    isset($_POST['wc_github_deployer_reset']) &&
    check_admin_referer('wc_github_deployer_reset_action', 'wc_github_deployer_reset_nonce')
  ) {
    delete_option('wc_github_deployer_token');
    delete_option('wc_github_deployer_repo');
    delete_option('wc_github_deployer_workflow');
    delete_option('wc_github_deployer_ref');
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Plugin data reset successfully!', 'wc-github-deployer') . '</p></div>';
  }
?>
  <div class="wrap">
    <h1><?php _e('GitHub Deploy Settings', 'wc-github-deployer'); ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields('wc_github_deployer_options'); ?>
      <?php do_settings_sections('wc_github_deployer_options'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php _e('GitHub Token (stored securely)', 'wc-github-deployer'); ?></th>
          <td><input type="password" name="wc_github_deployer_token" value="<?php echo esc_attr(get_option('wc_github_deployer_token')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Repository (user/repo)', 'wc-github-deployer'); ?></th>
          <td><input type="text" name="wc_github_deployer_repo" value="<?php echo esc_attr(get_option('wc_github_deployer_repo')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Workflow filename (.yml)', 'wc-github-deployer'); ?></th>
          <td><input type="text" name="wc_github_deployer_workflow" value="<?php echo esc_attr(get_option('wc_github_deployer_workflow')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Branch (ref)', 'wc-github-deployer'); ?></th>
          <td><input type="text" name="wc_github_deployer_ref" value="<?php echo esc_attr(get_option('wc_github_deployer_ref')); ?>" size="50" /></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
    <hr />
    <form method="post" style="margin-top:2em;">
      <?php wp_nonce_field('wc_github_deployer_reset_action', 'wc_github_deployer_reset_nonce'); ?>
      <input type="hidden" name="wc_github_deployer_reset" value="1" />
      <input type="submit" class="button button-secondary" value="<?php echo esc_attr__('Reset plugin data', 'wc-github-deployer'); ?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete all plugin data?', 'wc-github-deployer')); ?>');" />
    </form>
  </div>
<?php
}

/**
 * Core deployment function
 * 
 * Handles the actual API call to GitHub to trigger the workflow.
 * Retrieves stored settings and sends a POST request to the GitHub API.
 */
function wc_github_deployer_do_deploy()
{
  $token    = get_option('wc_github_deployer_token');
  $repo     = get_option('wc_github_deployer_repo');
  $workflow = get_option('wc_github_deployer_workflow');
  $ref      = get_option('wc_github_deployer_ref', 'main');

  // Don't proceed if required settings are missing
  if (!$token || !$repo || !$workflow) return;

  $url = "https://api.github.com/repos/$repo/actions/workflows/$workflow/dispatches";

  $args = [
    'method'    => 'POST',
    'headers'   => [
      'Authorization' => 'token ' . $token,
      'Content-Type'  => 'application/json',
      'User-Agent'    => 'WP-GitHub-Trigger'
    ],
    'body'      => json_encode([
      'ref' => $ref
    ]),
  ];

  $response = wp_remote_post($url, $args);

  if (is_wp_error($response)) {
    error_log('GitHub deploy error: ' . $response->get_error_message());
  } else {
    error_log('GitHub deploy triggered successfully!');
  }
}

/**
 * Trigger deployment when a post is saved
 * 
 * Hooks into WordPress save_post action to trigger GitHub workflow
 * when content is published or updated.
 * 
 * @param int $post_id The ID of the post being saved
 * @param WP_Post $post The post object
 */
add_action('save_post', 'wc_github_deployer_trigger', 10, 2);

function wc_github_deployer_trigger($post_id, $post)
{
  // Skip autosaves, revisions, or multiple calls
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (wp_is_post_revision($post_id)) return;
  if ($post->post_status !== 'publish') return;

  wc_github_deployer_do_deploy();
}

/**
 * Trigger deployment when a post is deleted
 * 
 * Hooks into WordPress before_delete_post action to trigger GitHub workflow
 * when content is removed from the site.
 * 
 * @param int $post_id The ID of the post being deleted
 */
add_action('before_delete_post', 'wc_github_deployer_trigger_delete', 10, 1);

function wc_github_deployer_trigger_delete($post_id)
{
  $post = get_post($post_id);
  if (!$post) return;
  wc_github_deployer_do_deploy();
}