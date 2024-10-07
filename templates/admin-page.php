<div class="wrap">
  <?php echo $alert ?>

  <h1>Pinghook</h1>
  <p>Posts a JSON webhook to an external API URL simply to notify when a post or page is published, updated, or deleted.</p>
  <h2>Settings</h2>
  <form method="post" action="">
    <table class="form-table">
      <tr valign="top">
          <th scope="row">API URL</th>
          <td><input type="text" name="pinghook_api_url" value="<?php echo esc_attr(get_option('pinghook_api_url')); ?>" size="50" /></td>
      </tr>
      <tr valign="top">
          <th scope="row">API secret</th>
          <td><input type="text" name="pinghook_api_secret" value="<?php echo esc_attr(get_option('pinghook_api_secret')); ?>" size="50" /></td>
      </tr>
    </table>
    <?php submit_button('Save Settings', 'primary', 'pinghook_save_settings'); ?>
  </form>
</div>
