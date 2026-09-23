<?php

namespace WPML\Troubleshooting\Engine\TranslationTablesOptimization\Infrastructure\Domain\TranslationElements\CompressFix;

use WPML\Troubleshooting\Engine\TranslationTablesOptimization\Core\Domain\MigrationDataService\ProcessorInterface;
use WPML\Translation\TranslationElements\FieldCompression;

class FixDoubleCompressionProcessor implements ProcessorInterface {

  private $wpdb;


  public function __construct( $wpdb ) {
    $this->wpdb = $wpdb;
  }


  public function process( array $records ): array {
    $processed  = [];
    $updateData = [];

    foreach ( $records as $record ) {
      $fieldDataResult = FieldCompression::fixDoubleCompression( $record->fieldData );
      $fieldData       = $fieldDataResult['data'];

      $fieldDataTranslatedResult = FieldCompression::fixDoubleCompression( $record->fieldDataTranslated );
      $fieldDataTranslated       = $fieldDataTranslatedResult['data'];

      if ( $fieldDataResult['was_double_compressed'] || $fieldDataTranslatedResult['was_double_compressed'] ) {
        $updateData[] = [
          'tid'                   => $record->tid,
          'field_data'            => $fieldData,
          'field_data_translated' => $fieldDataTranslated,
        ];
      }

      $processed[] = $record->tid;
    }

    if ( ! empty( $updateData ) ) {
      $this->bulkUpdateTranslateTable( $updateData );
    }

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
