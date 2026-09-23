<?php

namespace WPML\WPSEO\RankMathSEO\PrimaryCategory;

use WPML\WPSEO\Shared\PrimaryCategory\BaseHooks;

class Hooks extends BaseHooks {

	public function getMetaKeysMapping() {
		return [
			'rank_math_primary_category'    => 'category',
			'rank_math_primary_product_cat' => 'product_cat',
		];
	}
}
