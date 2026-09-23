<?php
/**
 * Dark "support amounts" section: the donation amounts picker (Vue, see
 * scripts/vue/donation) and the bank accounts as expandable cards.
 *
 * @var array $args bank_accounts (ACF repeater rows: title, content)
 */
$svg_url       = get_template_directory_uri() . '/assets/svg/donation/';
$bank_accounts = array_filter( $args['bank_accounts'] ?? [], function ( $row ) {
	return ! empty( $row['title'] );
} );
?>
<section class="donation-support">
    <img src="<?php echo esc_url( $svg_url . 'doodle-hand-heart.svg' ); ?>" width="368.471" height="200" alt="" class="donation-support__doodle donation-support__doodle--start" loading="lazy"/>
    <div class="donation-support__doodle donation-support__doodle--end" aria-hidden="true">
        <img src="<?php echo esc_url( $svg_url . 'doodle-heart.svg' ); ?>" width="77.5177" height="83.2467" alt="" class="donation-support__doodle-heart" loading="lazy"/>
        <img src="<?php echo esc_url( $svg_url . 'doodle-hand-box.svg' ); ?>" width="362.6" height="200.105" alt="" class="donation-support__doodle-box" loading="lazy"/>
    </div>

    <div class="donation-support__inner">
        <div id="donation-amounts-app" class="donation-support__picker"></div>

		<?php if ( $bank_accounts ) : ?>
            <div class="bank-accounts" data-reveal-stagger>
				<?php foreach ( array_values( $bank_accounts ) as $i => $account ) : ?>
                    <details class="bank-account<?php echo $i % 2 === 0 ? ' bank-account--pink' : ''; ?>">
                        <summary class="bank-account__summary">
                            <span class="bank-account__title"><?php echo esc_html( $account['title'] ); ?></span>
                            <img src="<?php echo esc_url( $svg_url . 'plus.svg' ); ?>" width="24" height="24" alt="" class="bank-account__icon"/>
                        </summary>
						<?php if ( ! empty( $account['content'] ) ) : ?>
                            <div class="bank-account__content"><?php echo wp_kses_post( $account['content'] ); ?></div>
						<?php endif; ?>
                    </details>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>
    </div>
</section>
