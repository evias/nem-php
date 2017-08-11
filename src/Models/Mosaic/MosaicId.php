<?php


namespace evias\NEMBlockchain\Models\Mosaic;


class MosaicId {

	protected $namespaceId;
	protected $name;

	public function __construct( $namespace, $mosaicName ) {
		$this->namespaceId = $namespace;
		$this->name        = $mosaicName;
	}

	public function toDTO() {
		return [
			'namespaceId' => $this->namespaceId,
			'name'        => $this->name,
		];
	}

}