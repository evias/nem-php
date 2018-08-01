<?php


namespace NEM\Infrastructure;

use NEM\NemSDK;

/**
 * This is the Mosaic Infrastructure service
 *
 * This service implements API endpoints of the NEM
 * Infrastructure.
 * 
 * @internal This class is currently *not* unit tested.
 *           Parts of this class may be malfunctioning or 
 *           not working all.
 */
class Mosaic 
	extends Service
{

    /**
     * The Base URL for this endpoint.
     *
     * @var string
     */
    protected $baseUrl = "/namespace/mosaic";

    /**
     * XXX
     *
     * @param namespace
     * @param id         - The topmost mosaic definition database id up to which root mosaic definitions are returned.
     *                   The parameter is optional. If not supplied the most recent mosaic definitiona are returned.
     * @param pageSize   - The number of mosaic definition objects to be returned for each request. The parameter is
     *                   optional. The default value is 25, the minimum value is 5 and hte maximum value is 100.
     */
    public function getMosaicDefinitionsPage($namespace, $id = null, $pageSize = null)
    {
        $params = ["namespace" => $namespace]
                + ($id !== null ? ["id" => $id] : [])
                + ($pageSize !== null ? ["pageSize" => $pageSize] : []);

        $apiUrl = $this->getPath('definition/page', $params);
        $response = $this->api->getJSON($apiUrl);

        //XXX include Error checks
        $object = json_decode($response, true);
        return $this->createBaseCollection($object["data"]);
    }

	/**
	 * Gets the mosaic definitions for a given namespace. The request supports paging.
	 *
	 * @returns Observable<MosaicDefinition[]>
	 /
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


	public function getProperties( $namespace, $mosaic ) {
		$mosaics = json_decode( $this->nemSDK->api->getJSON( "/namespace/mosaic/definition/page?namespace=" . $namespace, "" ) )->data;
		if ( is_array( $mosaics ) ) {
			foreach ( $mosaics as $key => $value ) {
				if ( $mosaic === $value->mosaic->id->name ) {
					return [
						'divisibility'  => intval( $value->mosaic->properties[0]->value ),
						'initialSupply' => intval( $value->mosaic->properties[1]->value ),
						'supplyMutable' => $value->mosaic->properties[2]->value === "true" ? true : false,
						'transferable'  => $value->mosaic->properties[3]->value === "true" ? true : false,
					];
				}
			}
		}

		return false;
    }
    */
}