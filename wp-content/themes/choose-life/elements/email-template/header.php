<?php
/**
 * Email layout, opening part: document, logo and the start of the pink card.
 * Colours follow the 2026 redesign (src/assets/scss/config/_colors.scss).
 * Inline styles and tables only: that's what email clients understand.
 *
 * @var array $args [ 'preheader' => string ]
 */

$img_url   = get_template_directory_uri() . '/assets/img/email/';
$preheader = $args['preheader'] ?? '';
$lang      = substr( get_locale(), 0, 2 );
?>
<!doctype html>
<html lang="<?php echo esc_attr( $lang ); ?>" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="x-apple-disable-message-reformatting">
	<title><?php bloginfo( 'name' ); ?></title>
	<!--[if !mso]><!-->
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700&display=swap" rel="stylesheet">
	<!--<![endif]-->
	<!--[if mso]>
	<xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
	<style>td, p, a, span, h1 { font-family: Arial, sans-serif !important; }</style>
	<![endif]-->
	<style>
		body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
		table, td { border-collapse: collapse; mso-table-lspace: 0; mso-table-rspace: 0; }
		img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
		.cl-email-content p { margin: 0 0 16px; }
		.cl-email-content p:last-child { margin-bottom: 0; }
		.cl-email-content a { color: #D91A21; }
		@media only screen and (max-width: 620px) {
			.cl-email-card { padding: 36px 22px !important; border-radius: 28px !important; }
			.cl-email-title { font-size: 34px !important; line-height: 42px !important; }
			.cl-email-summary { padding: 8px 20px !important; }
			.cl-email-total { font-size: 22px !important; }
		}
	</style>
</head>
<body style="margin:0;padding:0;background-color:#F8F4F3;">
<?php if ( $preheader ) : ?>
	<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;"><?php echo esc_html( $preheader ); ?></div>
<?php endif; ?>
<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#F8F4F3;">
	<tr>
		<td align="center" style="padding:32px 12px 40px;">
			<!--[if mso]><table role="presentation" width="600" border="0" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
			<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width:600px;">
				<tr>
					<td align="center" style="padding:0 0 28px;">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" style="text-decoration:none;">
							<img src="<?php echo esc_url( $img_url . 'logo.png' ); ?>" width="238" height="44" alt="<?php bloginfo( 'name' ); ?>" style="display:block;width:238px;height:44px;">
						</a>
					</td>
				</tr>
				<tr>
					<td class="cl-email-card" style="padding:48px 44px;border-radius:40px;background-color:#EEBEB1;font-family:Manrope,Arial,sans-serif;color:#1C1C1C;">
