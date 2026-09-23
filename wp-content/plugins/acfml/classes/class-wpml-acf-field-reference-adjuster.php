<?php


namespace ACFML;

class FieldReferenceAdjuster implements \IWPML_Backend_Action, \IWPML_Frontend_Action, \IWPML_DIC_Action {

	private $sitepress;
	private $originalReference;
	private $displayedPostId;
	const GROUP_POST_TYPE = 'acf-field-group';
	const FIELD_POST_TYPE = 'acf-field';
	private $fieldName;

	public function __construct( \SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public function add_hooks() {
		if ( $this->hooksShouldBeRegistered() ) {
			add_filter( 'acf/load_reference', [ $this, 'translatedFieldReference' ], 10, 3 );
		}
	}

	public function translatedFieldReference( $originalReference, $fieldName, $displayedPostId ) {
		$this->originalReference = $originalReference;
		$this->displayedPostId   = $displayedPostId;
		$this->fieldName         = $fieldName;

		if ( $this->referenceLanguageDifferentThanPostLanguage() ) {
			return $this->getTranslatedFieldReference();
		}

		return $originalReference;
	}

	private function hooksShouldBeRegistered() {
		return $this->isFrontEndRequest()
			&& $this->fieldGroupsAreTranslatable();
	}

	private function isFrontEndRequest() {
		return ! \is_admin();
	}

	private function fieldGroupsAreTranslatable() {
		return $this->sitepress->is_translated_post_type( self::GROUP_POST_TYPE );
	}

	private function referenceLanguageDifferentThanPostLanguage() {
		return $this->getFieldLanguage() !== $this->getPostLanguage();
	}

	private function getPostLanguage() {
		return $this->getLanguageCode( $this->displayedPostId );
	}

	private function getFieldLanguage() {
		$fieldsParent = $this->getFieldsParentByReference();
		if ( $fieldsParent ) {
			return $this->getLanguageCode( $fieldsParent, sprintf( 'post_%s', self::GROUP_POST_TYPE ) );
		}

		return '';
	}

	private function getLanguageCode( $postId, $postType = 'post_post' ) {
		$languageDetails = $this->sitepress->get_element_language_details( $postId, $postType );

		return isset( $languageDetails->language_code ) ? (string) $languageDetails->language_code : '';
	}

	private function getTranslatedFieldReference() {
		$fieldsParent = $this->getFieldsParentByReference();
		if ( $fieldsParent ) {
			$elementType           = sprintf( 'post_%s', self::GROUP_POST_TYPE );
			$trid                  = $this->sitepress->get_element_trid( $fieldsParent, $elementType );
			$translatedFieldGroups = $this->sitepress->get_element_translations( $trid, $elementType, false, true );
			$translatedFieldGroup  = isset( $translatedFieldGroups[ $this->sitepress->get_current_language() ]->element_id )
				? (int) $translatedFieldGroups[ $this->sitepress->get_current_language() ]->element_id
				: 0;

			if ( $translatedFieldGroup && 'publish' === get_post_status( $translatedFieldGroup ) ) {
				$translatedReference = $this->getReferenceFromChildrenOfFieldGroup( $translatedFieldGroup );

				return $translatedReference ?: $this->originalReference;
			}
		}

		return $this->originalReference;
	}

	private function getFieldsParentByReference() {
		$posts = get_posts(
			[
				'name'        => $this->originalReference,
				'post_type'   => self::FIELD_POST_TYPE,
				'numberposts' => 1,
			]
		);

		return ( isset( $posts[0] ) && is_a( $posts[0], \WP_Post::class ) ) ? $posts[0]->post_parent : null;
	}

	private function getReferenceFromChildrenOfFieldGroup( $groupId ) {
		$posts = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => self::FIELD_POST_TYPE,
				'post_parent' => $groupId,
				'post_status' => 'publish',
			]
		);
		foreach ( $posts as $post ) {
			if ( $post->post_excerpt === $this->fieldName ) {
				return $post->post_name;
			}
		}

		return null;
	}
}
