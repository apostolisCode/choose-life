<?php

use WPML\FP\Logic;

class WPML_ACF {

	public function init_worker() {
		if ( ! self::is_acf_active() ) {
			return;
		}

		( new \WPML_Action_Filter_Loader() )->load( [
			\ACFML\FieldGroup\SetDefaultsFactory::class,
			\ACFML\FieldGroup\PreflightHooks::class,
		] );

		if ( self::isWpmlSetupComplete() ) {
			\ACFML\Upgrade\Upgrade::init();

			$loaders = wpml_collect(
				[
					\ACFML\Strings\HooksFactory::class             => true,
					\ACFML\FieldPreferences\TranslationJobs::class => true,
					\ACFML\FieldPreferences\SubfieldRules::class   => true,
					\WPML_ACF_Migrate_Option_Page_Strings::class   => true,
					\ACFML\MigrateBlockPreferences::class          => true,
					\WPML_ACF_Field_Groups::class                  => true,
					\WPML_ACF_Xliff::class                         => $this->can_create_xliff(),
					\WPML_ACF_Pro::class                           => true,
					\WPML_ACF_Field_Annotations::class             => true,
					\WPML_ACF_Location_Rules::class                => true,
					\WPML_ACF_Attachments::class                   => true,
					\WPML_ACF_Field_Settings::class                => true,
					\WPML_ACF_Blocks::class                        => true,
					\WPML_ACF_Editor_Hooks::class                  => true,
					\WPML_ACF_Display_Translated::class            => true,
					\WPML_ACF_Worker::class                        => true,
					\ACFML\FieldReferenceAdjuster::class           => true,
					\ACFML\Tools\Export::class                     => true,
					\ACFML\Tools\Import::class                     => true,
					\ACFML\Tools\Local::class                      => true,
					\WPML_ACF_Translatable_Groups_Checker::class   => true,
					\ACFML\FieldGroup\SetCptNotTranslatable::class => true,
					\ACFML\Cpt\HooksFactory::class                 => true,
					\ACFML\Field\FrontendHooks::class              => true,
					\ACFML\Field\LinkUrlLoadHooks::class           => true,
					\ACFML\Field\LinkJobAttributes::class          => true,
					\ACFML\Field\ConditionalLogic::class           => true,
					\ACFML\FieldGroup\HooksFactory::class          => true,
					\ACFML\FieldGroup\BlockPreferenceReapplyHooks::class => true,
					\ACFML\FieldGroup\SetupScreen::class           => true,
					\ACFML\OptionsPage\HooksFactory::class         => $this->areOptionPagesAvailable(),
					\ACFML\Options\HooksFactory::class             => $this->areOptionPagesAvailable(),
					\ACFML\Taxonomy\HooksFactory::class            => true,
					\ACFML\Notice\FieldGroupModes::class           => true,
					\ACFML\Notice\ActivationNotice::class          => true,
					\ACFML\Notice\MediaTexts::class                => true,
					\ACFML\Post\EditorHooksFactory::class          => true,
					\ACFML\Post\MaxInputVarsGuard::class           => true,
					\ACFML\Ajax\LanguageScope::class               => true,
					\ACFML\TranslationEditor\DisableHooks::class   => true,
					\ACFML\Post\AutoTranslateHooks::class          => true,
					\ACFML\Post\JobReviewHooks::class              => true,
					\ACFML\TranslationEditor\JobFilter::class      => true,
					\ACFML\Repeater\Sync\HooksFactory::class       => true,
					\ACFML\Repeater\Sync\JobHooks::class           => true,
					\ACFML\Repeater\Sync\SignatureHooks::class     => true,
					\ACFML\Cache\Flush::class                      => true,
				]
			)
				->filter( Logic::isTruthy() )
				->keys()
				->toArray();

			( new \WPML_Action_Filter_Loader() )->load( $loaders );
		}
	}

	public static function is_acf_active() {
		return class_exists( 'ACF' );
	}

	public static function isWpmlSetupComplete() {
		return (bool) apply_filters( 'wpml_setting', false, 'setup_complete' );
	}

	private function can_create_xliff() {
		return defined( 'WPML_ACF_XLIFF_SUPPORT' ) && WPML_ACF_XLIFF_SUPPORT && is_admin() && class_exists( 'acf' );
	}

	private function areOptionPagesAvailable() {
		return function_exists( 'acf_get_options_page' );
	}
}
