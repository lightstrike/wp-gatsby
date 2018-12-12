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
				<th scope="row"><?php _e( 'Preview Settings', 'wp-gatsby' ); ?></th>
        <td><p>Checking `Activate Preview?` below will automatically push updates and autosaves to your Gatsby development environment.</p></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Activate Preview?', 'wp-gatsby' ); ?></th>
        <td>
          <input type='checkbox' name='gatsby_options[preview][activated]' <?php checked( $options['preview']['activated'], 1 ); ?> value='1'>
        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Gatsby Preview Refresh Token', 'wp-gatsby' ); ?></th>
				<td>
					<input type='text' name='gatsby_options[preview][refresh_token]' value='<?php echo $options['preview']['refresh_token'] ?>' style="width: 500px; max-width: 100%;">
					<p>Set this equal to the GATSBY_REFRESH_TOKEN value in `gatsby-config.js`. This enables authenticating on the `__refresh` endpoint.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Gatsby Preview Refresh Hook', 'wp-gatsby' ); ?></th>
        <td>
          <input type='text' name='gatsby_options[preview][build_hook]' value='<?php echo $options['preview']['build_hook'] ?>' style="width: 500px; max-width: 100%;">
					<p>Set this equal to your development URL, with `__refresh` appended. (ex. `http://dev.gatsbyjs.org/__refresh`)</p>
        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Autosave Interval', 'wp-gatsby' ); ?></th>
        <td>
        <input type='number' name='gatsby_options[preview][autosave_interval]' value='<?php echo $options['preview']['autosave_interval'] ?>' style="width: 500px; max-width: 100%;">
        </td>
			</tr>
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
				<th scope="row"><?php _e( 'Are you using WP REST API Cache?', 'wp-gatsby' ); ?></th>
        <td>
          <input type='checkbox' name='gatsby_options[cache][wp_rest_api_cache]' <?php checked( $options['cache']['wp_rest_api_cache'], 1 ); ?> value='1'>
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
