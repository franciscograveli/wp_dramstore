<?php

namespace DgoraWcas\Analytics;

use WC_CSV_Exporter;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSVExporter extends WC_CSV_Exporter {

	private $context = '';
	private $lang    = '';

	public function set_context( $context = '' ) {
		$this->context = $context;
	}

	public function set_lang( $lang = '' ) {
		$this->lang = $lang;
	}

	public function prepare_data_to_export() {
		$data = new Data();
		if ( ! empty( $this->lang ) ) {
			$data->setLang( $this->lang );
		}

		$dateSuffix = date( 'Ymd-His', time() );

		if ( in_array( $this->context, [ 'autocomplete', 'search-results-page' ] ) ) {
			$data->setContext( $this->context );
			$this->set_filename( 'fibosearch-analytics_' . $this->context . '_' . ( empty( $this->lang ) ? '' : $this->lang . '_' ) . $dateSuffix );
		} else {
			//phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
			$this->set_filename( 'fibosearch-analytics_critical' . '_' . ( empty( $this->lang ) ? '' : $this->lang . '_' ) . $dateSuffix );
		}

		$this->set_column_names(
			[
				'phrase' => 'Phrase',
				'qty'    => 'Repetitions',
			]
		);

		if ( empty( $this->context ) ) {
			$this->row_data = $data->getCriticalSearches( PHP_INT_MAX );
		} else {
			$this->row_data = $data->getPhrasesWithResults( PHP_INT_MAX );
		}
	}
}
