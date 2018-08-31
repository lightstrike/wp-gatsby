<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

settings_errors(); ?>
<div class="wrap">
	<h1><?php _e( 'WP Gatsby', 'wp-gatsby' ); ?></h1>
	<form action="<?php echo admin_url( 'options-general.php?page=wp-gatsby' ); ?>" method="POST">
		<?php wp_nonce_field( 'gatsby_options', 'gatsby_nonce' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Publish to Gatsby', 'wp-gatsby' ); ?></th>
				<td><a href="<?php echo self::_publish_to_gatsby_url(); ?>" class="button button-primary"><?php _e( 'Publish Now', 'wp-gatsby' ); ?></a></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Live URL', 'wp-gatsby' ); ?></th>
				<td>
					<input type='text' name='gatsby_options[url]' value='<?php echo $options['url'] ?>' style="width: 500px; max-width: 100%;">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Preview Settings', 'wp-gatsby' ); ?></th>
        <td><p>The draft ID is always used as the unique identifier for each draft in custom URLs.</p></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Use Custom Preview URL?', 'wp-gatsby' ); ?></th>
        <td>
          <input type='checkbox' name='gatsby_options[preview][activated]' <?php checked( $options['preview']['activated'], 1 ); ?> value='1'>
        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Preview URL Base', 'wp-gatsby' ); ?></th>
        <td>
        <input type='text' name='gatsby_options[preview][base]' value='<?php echo $options['preview']['base'] ?>' style="width: 500px; max-width: 100%;">
        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Netlify Settings', 'wp-gatsby' ); ?></th>
        <td><p>Create build hook in https://app.netlify.com/sites/{{ site_name }}/settings/deploys#build-hooks and paste the URL below.</p></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Auto Deploy on Post Update/Publish?', 'wp-gatsby' ); ?></th>
        <td>
          <input type='checkbox' name='gatsby_options[netlify][auto_publish]' <?php checked( $options['netlify']['auto_publish'], 1 ); ?> value='1'>
        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Netlify Build Hook', 'wp-gatsby' ); ?></th>
        <td>
          <input type='text' name='gatsby_options[netlify][build_hook]' value='<?php echo $options['netlify']['build_hook'] ?>' style="width: 500px; max-width: 100%;">
        </td>
			</tr>
			<tr>
				<th scope="row">&nbsp;</th>
				<td><input type="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-gatsby' ); ?>"></td>
			</tr>
		</table>
	</form>
</div>
