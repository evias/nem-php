<?php


namespace evias\NEMBlockchain\Infrastructure;

use evias\NEMBlockchain\NemSDK;

class Mosaic {

	public $nemSDK;
	public $endpoint = '/namespace/';

	public function __construct( NemSDK $nemSDK ) {
		$this->nemSDK = $nemSDK;
	}

	/**
	 * Gets the mosaic definitions for a given namespace. The request supports paging.
	 *
	 * @param namespace
	 * @param id         - The topmost mosaic definition database id up to which root mosaic definitions are returned.
	 *                   The parameter is optional. If not supplied the most recent mosaic definitiona are returned.
	 * @param pageSize   - The number of mosaic definition objects to be returned for each request. The parameter is
	 *                   optional. The default value is 25, the minimum value is 5 and hte maximum value is 100.
	 *
	 * @returns Observable<MosaicDefinition[]>
	 */
	private function getMosaicDefinitionsPage( $namespace, $id = null, $pageSize = null ) {
		$query = 'namespace=' . $namespace;
		if ( $id !== null ) {
			$query .= '&id=' . $id;
		}
		if ( $pageSize !== null ) {
			$query .= '&pageSize=' . $pageSize;
		}

		return json_decode( $this->nemSDK->api->getJSON( $this->endpoint . 'mosaic/definition/page?' . $query, "" ) )->data;
	}

	public function getMosaicDefinitions( $namespace, $rows = 100, $id = null ) {
		$complete = false;
		$upNext   = min( 100, $rows );
		$mosaics  = $this->getMosaicDefinitionsPage( $namespace, $id, $upNext );
		$lastId   = ( end( $mosaics ) )->meta->id;;
		$rows = $rows - $upNext;

		while ( $complete === false && $rows > 0 ) {
			$upNext = min( 100, $rows );
			$next   = $this->getMosaicDefinitionsPage( $namespace, $lastId, $upNext );
			if ( count( $next ) < 1 ) {
				$complete = true;
			}
			$mosaics = array_merge( $mosaics, $next );
			$lastId  = ( end( $mosaics ) )->meta->id;;
			$rows -= $upNext;
		}

		return $mosaics;
	}


}