<?php
/**
 * CF7-Template: Newsletter Form
 *
 * Newsletter sign-up on the homepage (elements/sections/feature-card.php).
 * Mail tags: [email]
 */

$privacy_url  = get_privacy_policy_url();
$privacy_link = $privacy_url
	? sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $privacy_url ), esc_html__( 'Privacy Policy', 'choose-life' ) )
	: esc_html__( 'Privacy Policy', 'choose-life' );
?>
<div class="newsletter-form">
    <div class="newsletter-form__row">
        <label for="newsletter-email" class="visually-hidden"><?php esc_html_e( 'Email', 'choose-life' ); ?></label>
        [email* email id:newsletter-email class:newsletter-form__input autocomplete:email placeholder "<?php echo esc_attr__( 'Email', 'choose-life' ); ?>"]
        [submit class:newsletter-form__submit "<?php echo esc_attr__( 'Subscribe', 'choose-life' ); ?>"]
    </div>

    <div class="newsletter-form__consent">
        [acceptance newsletter-consent]<?php
		/* translators: %s: link to the privacy policy page */
		printf( esc_html__( 'I accept the %s and agree to receive the newsletter by email.', 'choose-life' ), $privacy_link );
		?>[/acceptance]
    </div>
</div>
