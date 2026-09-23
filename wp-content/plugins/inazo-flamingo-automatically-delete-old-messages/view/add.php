<?php
if( !defined( 'ABSPATH' ) ){
	
	exit(-1);
}


echo '<div class="wrap">';
echo '<h1>'.__('Configuration des délais de suppression automatique pour flamingo','inazo-flamingo-auto-trash').'</h1>';
?>
<form method="post" action=""> 

<div id="poststuff">
	
	<div class="postbox">
		<div class="inside">
		
			<p>
				<label><?php echo __('Supprimer tous les contacts de plus de ','inazo-flamingo-auto-trash');?></label>
				<input type="text" name="days_contact" value="<?php echo get_option('inazo_flamingo_auto_trash_contact_days','365'); ?>" required />
				<?php echo __('jours','inazo-flamingo-auto-trash');?>
			</p>
			
			<p>
				<label><?php echo __('Supprimer tous les messages entrant de plus de ','inazo-flamingo-auto-trash');?></label>
				<input type="text" name="days_inbound" value="<?php echo get_option('inazo_flamingo_auto_trash_inbound_days','365'); ?>" required />
				<?php echo __('jours','inazo-flamingo-auto-trash');?>
			</p>
			
			<p>
				<label><?php echo __('Supprimer tous les messages sortant de plus de ','inazo-flamingo-auto-trash');?></label>
				<input type="text" name="days_outbound" value="<?php echo get_option('inazo_flamingo_auto_trash_outbound_days','365'); ?>" required />
				<?php echo __('jours','inazo-flamingo-auto-trash');?>
			</p>
			
			<p>
				<label><?php echo __('Supprimer tous les messages en spam de plus de ','inazo-flamingo-auto-trash');?></label>
				<input type="text" name="days_spam" value="<?php echo get_option('inazo_flamingo_auto_trash_spam_days','30'); ?>" required />
				<?php echo __('jours','inazo-flamingo-auto-trash');?>
			</p>
			
			<div class="clear"></div>
			<p>
				<?php submit_button(); ?>
			</p>
		</div>
	</div>
</div>


<?php 

wp_nonce_field( 'inazo.flamingo.auto.trash.config', 'token_csrf_action_edit' ); 

?>

</form>
</div>