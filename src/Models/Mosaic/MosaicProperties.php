<?php


namespace evias\NEMBlockchain\Models\Mosaic;


class MosaicProperties {
	public $divisibility;
	public $initialSupply;
	public $supplyMutable;
	public $transferable;

	public function __construct( $divisibility, $initialSupply, $supplyMutable, $transferable ) {
		$this->divisibility  = $divisibility;
		$this->initialSupply = $initialSupply;
		$this->supplyMutable = $supplyMutable;
		$this->transferable  = $transferable;
	}

	public function toDTO() {
		return [
			[
				"name"  => "divisibility",
				"value" => strval( $this->divisibility ),
			],
			[
				"name"  => "initialSupply",
				"value" => strval( $this->initialSupply ),
			],
			[
				"name"  => "supplyMutable",
				"value" => ( $this->supplyMutable ) ? "true" : "false",
			],
			[
				"name"  => "transferable",
				"value" => ( $this->transferable ) ? "true" : "false",
			],
		];
	}

}