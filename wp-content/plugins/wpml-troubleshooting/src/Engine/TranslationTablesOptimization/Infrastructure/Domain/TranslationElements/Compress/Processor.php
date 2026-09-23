<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\Compress;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\ProcessorInterface;
use WPML\Translation\TranslationElements\FieldCompression;

class Processor implements ProcessorInterface {

  private $wpdb;


  public function __construct( $wpdb ) {
    $this->wpdb = $wpdb;
  }


  public function process( array $records ): array {
    $processed  = [];
    $updateData = [];

    foreach ( $records as $record ) {
      $compressedFieldData           = FieldCompression::compress( $record->fieldData );
      $compressedFieldDataTranslated = FieldCompression::compress( $record->fieldDataTranslated );

      $updateData[] = [
        'tid'                   => $record->tid,
        'field_data'            => $compressedFieldData,
        'field_data_translated' => $compressedFieldDataTranslated,
      ];

      $processed[] = $record->tid;
    }

    $this->bulkUpdateTranslateTable( $updateData );

    return $processed;
  }


  private function bulkUpdateTranslateTable( array $data ) {
    if ( empty( $data ) ) {
      return;
    }

    $fieldDataCases = [];
    $fieldDataTranslatedCases = [];
    $tidValues = [];

		$fieldDataArgs           = [];
		$fieldDataTranslatedArgs = [];
		foreach ( $data as $record ) {
			$tid                       = (int) $record['tid'];
			$fieldDataCases[]          = 'WHEN tid = %d THEN %s';
			$fieldDataArgs[]           = $tid;
			$fieldDataArgs[]           = (string) $record['field_data'];
			$fieldDataTranslatedCases[] = 'WHEN tid = %d THEN %s';
			$fieldDataTranslatedArgs[]  = $tid;
			$fieldDataTranslatedArgs[]  = (string) $record['field_data_translated'];
			$tidValues[]               = $tid;
		}

		$args = array_merge( $fieldDataArgs, $fieldDataTranslatedArgs, $tidValues );

		$wpdb = $this->wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}icl_translate SET
				field_data = CASE " . implode( ' ', array_fill( 0, count( $tidValues ), 'WHEN tid = %d THEN %s' ) ) . " END,
				field_data_translated = CASE " . implode( ' ', array_fill( 0, count( $tidValues ), 'WHEN tid = %d THEN %s' ) ) . " END
				WHERE tid IN (" . implode( ', ', array_fill( 0, count( $tidValues ), '%d' ) ) . ')',
				$args
			)
		);
	}


}
