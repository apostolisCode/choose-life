<?php
/**
 * CF7-Template: Contact Form
 *
 * Fields of the contact form (templates/contact.php). Mail tags:
 * [full-name] [email] [country] [subject] [message]
 */

$privacy_url  = get_privacy_policy_url();
$privacy_link = $privacy_url
	? sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $privacy_url ), esc_html__( 'Privacy Policy', 'choose-life' ) )
	: esc_html__( 'Privacy Policy', 'choose-life' );
?>
<div class="contact-form">
    <div class="contact-form__grid">
        <div class="contact-form__field">
            <label for="contact-full-name" class="contact-form__label"><?php esc_html_e( 'Full name', 'choose-life' ); ?> *</label>
            [text* full-name id:contact-full-name class:contact-form__input autocomplete:name]
        </div>
        <div class="contact-form__field">
            <label for="contact-email" class="contact-form__label"><?php esc_html_e( 'Email', 'choose-life' ); ?> *</label>
            [email* email id:contact-email class:contact-form__input autocomplete:email]
        </div>
        <div class="contact-form__field">
            <label for="contact-country" class="contact-form__label"><?php esc_html_e( 'Country', 'choose-life' ); ?> *</label>
            <?php // countries from the Listo plugin, Greek names + Greece first: see template-hooks.php ?>
            [select* country id:contact-country class:contact-form__input class:contact-form__input--select autocomplete:country-name default:1 data:countries]
        </div>
        <div class="contact-form__field">
            <label for="contact-subject" class="contact-form__label"><?php esc_html_e( 'Subject', 'choose-life' ); ?></label>
            [text subject id:contact-subject class:contact-form__input]
        </div>
        <div class="contact-form__field contact-form__field--wide">
            <label for="contact-message" class="contact-form__label"><?php esc_html_e( 'Message', 'choose-life' ); ?></label>
            [textarea message id:contact-message class:contact-form__input class:contact-form__input--textarea]
        </div>
    </div>

    <div class="contact-form__consent">
        [acceptance privacy-consent]<?php
		/* translators: %s: link to the privacy policy page */
		printf( esc_html__( 'I have read and accept the %s and consent to the processing of my personal data for handling my request.', 'choose-life' ), $privacy_link );
		?>[/acceptance]
    </div>

    <div class="contact-form__submit">
        [submit class:cl-btn class:cl-btn--primary class:cl-btn--lg "<?php esc_attr_e( 'Send', 'choose-life' ); ?>"]
    </div>
</div>
