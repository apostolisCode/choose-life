<?php
/**
 * Email body, inside the card of header.php / footer.php: title with the red
 * underline and the text, plus (thank you email) the star and the donation
 * summary of the checkout thank you page. Built by Inc_Email::render().
 *
 * @var array $args [
 *     'icon'    => bool, the star above the title
 *     'title'   => string,
 *     'content' => string (HTML),
 *     'rows'    => [ [ 'label' => string, 'value' => string ], ... ], optional
 *     'total'   => [ 'label' => string, 'value' => string ], with the rows
 *     'button'  => [ 'url' => string, 'label' => string ], optional
 * ]
 */

$img_url = get_template_directory_uri() . '/assets/img/email/';
$font    = 'font-family:Manrope,Arial,sans-serif;';
?>
<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
	<?php if ( ! empty( $args['icon'] ) ) : ?>
		<tr>
			<td align="center" style="padding:0 0 18px;">
				<img src="<?php echo esc_url( $img_url . 'star.png' ); ?>" width="60" height="60" alt="" style="display:block;width:60px;height:60px;">
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td align="center" class="cl-email-title" style="<?php echo $font; ?>font-size:44px;line-height:52px;font-weight:700;color:#1C1C1C;">
			<?php echo esc_html( $args['title'] ); ?>
		</td>
	</tr>
	<tr>
		<td align="center" style="padding:10px 0 26px;">
			<img src="<?php echo esc_url( $img_url . 'underline.png' ); ?>" width="180" height="10" alt="" style="display:block;width:180px;height:10px;">
		</td>
	</tr>
	<tr>
		<td class="cl-email-content" style="<?php echo $font; ?>font-size:17px;line-height:28px;color:#0A0A0A;">
			<?php echo wp_kses_post( $args['content'] ); ?>
		</td>
	</tr>
	<?php if ( ! empty( $args['rows'] ) ) : ?>
	<tr>
		<td style="padding:32px 0 0;">
			<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-radius:28px;background-color:#F8F4F3;">
				<tr>
					<td class="cl-email-summary" style="padding:12px 32px;">
						<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
							<?php foreach ( $args['rows'] as $row ) : ?>
								<tr>
									<td valign="top" style="padding:12px 12px 12px 0;<?php echo $font; ?>font-size:15px;line-height:22px;color:#0A0A0A;"><?php echo esc_html( $row['label'] ); ?></td>
									<td valign="top" align="right" style="padding:12px 0;<?php echo $font; ?>font-size:15px;line-height:22px;font-weight:700;color:#1C1C1C;"><?php echo esc_html( $row['value'] ); ?></td>
								</tr>
							<?php endforeach; ?>
							<tr>
								<td colspan="2" style="padding:8px 0 0;">
									<div style="height:0;border-top:1.5px dashed #1C1C1C;line-height:0;font-size:0;">&nbsp;</div>
								</td>
							</tr>
							<tr>
								<td style="padding:16px 12px 16px 0;<?php echo $font; ?>font-size:17px;line-height:24px;font-weight:700;color:#1C1C1C;"><?php echo esc_html( $args['total']['label'] ); ?></td>
								<td align="right" class="cl-email-total" style="padding:16px 0;<?php echo $font; ?>font-size:26px;line-height:32px;font-weight:700;color:#1C1C1C;"><?php echo esc_html( $args['total']['value'] ); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php endif; ?>
	<?php if ( ! empty( $args['button'] ) ) : ?>
		<tr>
			<td align="center" style="padding:32px 0 0;">
				<!--[if mso]>
				<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="<?php echo esc_url( $args['button']['url'] ); ?>" style="height:56px;v-text-anchor:middle;width:280px;" arcsize="50%" stroke="f" fillcolor="#D91A21">
					<center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:17px;font-weight:bold;"><?php echo esc_html( $args['button']['label'] ); ?></center>
				</v:roundrect>
				<![endif]-->
				<!--[if !mso]><!-->
				<a href="<?php echo esc_url( $args['button']['url'] ); ?>" target="_blank" style="display:inline-block;padding:16px 32px;border-radius:999px;background-color:#D91A21;<?php echo $font; ?>font-size:17px;line-height:24px;font-weight:700;color:#FFFFFF;text-decoration:none;"><?php echo esc_html( $args['button']['label'] ); ?> &rarr;</a>
				<!--<![endif]-->
			</td>
		</tr>
	<?php endif; ?>
</table>
