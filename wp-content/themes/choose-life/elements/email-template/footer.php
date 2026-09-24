<?php
/**
 * Email layout, closing part: the end of the card and the contact details
 * of Theme Options → Footer.
 */

$contact_email = get_field( 'contact_email', 'options' );
$contact_phone = get_field( 'contact_phone', 'options' );
$contact       = array_filter( [
	$contact_email ? sprintf( '<a href="mailto:%1$s" style="color:#747272;text-decoration:underline;">%1$s</a>', esc_html( $contact_email ) ) : '',
	$contact_phone ? sprintf( '<a href="tel:%s" style="color:#747272;text-decoration:none;">%s</a>', esc_attr( preg_replace( '/[^\d+]/', '', $contact_phone ) ), esc_html( $contact_phone ) ) : '',
] );
?>
					</td>
				</tr>
				<tr>
					<td align="center" style="padding:28px 16px 0;font-family:Manrope,Arial,sans-serif;font-size:13px;line-height:20px;color:#747272;">
						<?php if ( $contact ) : ?>
							<p style="margin:0 0 6px;"><?php esc_html_e( 'Questions? Contact us:', 'choose-life' ); ?></p>
							<p style="margin:0 0 14px;"><?php echo implode( ' &nbsp;·&nbsp; ', $contact ); ?></p>
						<?php endif; ?>
						<p style="margin:0;">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" style="color:#747272;text-decoration:none;">&copy; <?php echo esc_html( date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></a>
						</p>
					</td>
				</tr>
			</table>
			<!--[if mso]></td></tr></table><![endif]-->
		</td>
	</tr>
</table>
</body>
</html>
