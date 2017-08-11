<?php


namespace evias\NEMBlockchain\Models\Mosaic;


class MosaicLevy {

	protected $type;
	protected $recipient;
	protected $fee;
	protected $mosaicId;

	public function __construct( $type, $recipient, $fee, $mosaicId ) {
		$this->type      = $type;
		$this->recipient = $recipient;
		$this->fee       = $fee;
		$this->mosaicId  = $mosaicId;
	}

	public function toDTO() {

		return [
			'type'      => $this->type,
			'recipient' => $this->recipient,
			'mosaicId'  => $this->mosaicId,
			'fee'       => $this->fee,
		];

	}


}

