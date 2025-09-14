<?php

/**
 * Plugin Name: Deploy Trigger for GitHub
 * Description: Trigger GitHub Actions workflow from WordPress when a post is saved or deleted.
 * Version: 1.4
 * Author: facudev
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: deploy-trigger-for-github
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Register plugin settings
 * 
 * Sets up the options needed for GitHub integration and registers them
 * with WordPress settings API for proper sanitization and handling.
 */
function depltrfo_register_settings()
{
  add_option('depltrfo_token', '');
  add_option('depltrfo_repo', '');
  add_option('depltrfo_workflow', '');
  add_option('depltrfo_ref', 'main');

  register_setting('depltrfo_options', 'depltrfo_token', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  register_setting('depltrfo_options', 'depltrfo_repo', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  register_setting('depltrfo_options', 'depltrfo_workflow', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  register_setting('depltrfo_options', 'depltrfo_ref', [
    'sanitize_callback' => 'sanitize_text_field',
  ]);
}
add_action('admin_init', 'depltrfo_register_settings');

/**
 * Add settings page to WordPress admin
 * 
 * Creates a new menu item under Settings for configuring the GitHub deployment options.
 */
function depltrfo_menu()
{
  add_options_page(
    'GitHub Deploy Settings',
    'GitHub Deploy',
    'manage_options',
    'deploy-trigger-for-github',
    'depltrfo_options_page'
  );
}
add_action('admin_menu', 'depltrfo_menu');

/**
 * Render the settings page HTML
 * 
 * Displays the form for configuring GitHub integration settings
 * and handles the reset functionality for plugin data.
 */
function depltrfo_options_page()
{
  // Process reset if form was submitted
  if (
    isset($_POST['depltrfo_reset']) &&
    check_admin_referer('depltrfo_reset_action', 'depltrfo_reset_nonce')
  ) {
    delete_option('depltrfo_token');
    delete_option('depltrfo_repo');
    delete_option('depltrfo_workflow');
    delete_option('depltrfo_ref');
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Plugin data reset successfully!', 'deploy-trigger-for-github') . '</p></div>';
  }
?>
  <div class="wrap">
    <h1><?php esc_html_e('GitHub Deploy Settings', 'deploy-trigger-for-github'); ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields('depltrfo_options'); ?>
      <?php do_settings_sections('depltrfo_options'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php esc_html_e('GitHub Token (stored securely)', 'deploy-trigger-for-github'); ?></th>
          <td><input type="password" name="depltrfo_token" value="<?php echo esc_attr(get_option('depltrfo_token')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php esc_html_e('Repository (user/repo)', 'deploy-trigger-for-github'); ?></th>
          <td><input type="text" name="depltrfo_repo" value="<?php echo esc_attr(get_option('depltrfo_repo')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php esc_html_e('Workflow filename (.yml)', 'deploy-trigger-for-github'); ?></th>
          <td><input type="text" name="depltrfo_workflow" value="<?php echo esc_attr(get_option('depltrfo_workflow')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php esc_html_e('Branch (ref)', 'deploy-trigger-for-github'); ?></th>
          <td><input type="text" name="depltrfo_ref" value="<?php echo esc_attr(get_option('depltrfo_ref')); ?>" size="50" /></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
    <hr />
    <form method="post" style="margin-top:2em;">
      <?php wp_nonce_field('depltrfo_reset_action', 'depltrfo_reset_nonce'); ?>
      <input type="hidden" name="depltrfo_reset" value="1" />
      <input type="submit" class="button button-secondary" value="<?php echo esc_attr__('Reset plugin data', 'deploy-trigger-for-github'); ?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete all plugin data?', 'deploy-trigger-for-github')); ?>');" />
    </form>
  </div>
<?php
}

/**
 * Optional debug logging (only active when WP_DEBUG is enabled)
 */
function depltrfo_debug_logging()
{
  if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('depltrfo_error', function ($message) {
      // Use wp_debug_log instead of error_log
      if (function_exists('wp_debug_log')) {
        wp_debug_log('GitHub deploy error: ' . $message);
      }
    });

    add_action('depltrfo_success', function () {
      // Use wp_debug_log instead of error_log
      if (function_exists('wp_debug_log')) {
        wp_debug_log('GitHub deploy triggered successfully!');
      }
    });
  }
}
add_action('init', 'depltrfo_debug_logging');

/**
 * Core deployment function
 * 
 * Handles the actual API call to GitHub to trigger the workflow.
 * Retrieves stored settings and sends a POST request to the GitHub API.
 */
function depltrfo_do_deploy()
{
  $token    = get_option('depltrfo_token');
  $repo     = get_option('depltrfo_repo');
  $workflow = get_option('depltrfo_workflow');
  $ref      = get_option('depltrfo_ref', 'main');

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
    // Use a custom action instead of error_log
    do_action('depltrfo_error', $response->get_error_message());
  } else {
    // Use a custom action for successful deployment
    do_action('depltrfo_success');
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
add_action('save_post', 'depltrfo_trigger', 10, 2);

function depltrfo_trigger($post_id, $post)
{
  // Skip autosaves, revisions, or multiple calls
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (wp_is_post_revision($post_id)) return;
  if ($post->post_status !== 'publish') return;

  depltrfo_do_deploy();
}

/**
 * Trigger deployment when a post is deleted
 * 
 * Hooks into WordPress before_delete_post action to trigger GitHub workflow
 * when content is removed from the site.
 * 
 * @param int $post_id The ID of the post being deleted
 */
add_action('before_delete_post', 'depltrfo_trigger_delete', 10, 1);

function depltrfo_trigger_delete($post_id)
{
  $post = get_post($post_id);
  if (!$post) return;
  depltrfo_do_deploy();
}
