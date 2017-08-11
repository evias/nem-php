<?php


namespace evias\NEMBlockchain\Models\Transaction;

use evias\NEMBlockchain\NemSDK;

class Multisig {

	public $nemSDK;
	public $multisigPublicKey;
	public $multisigPrivateKey;
	public $otherTrans;

	public function __construct( NemSDK $nemSDK, $multisigPublicKey, $multisigPrivateKey ) {
		$this->nemSDK             = $nemSDK;
		$this->multisigPublicKey  = $multisigPublicKey;
		$this->multisigPrivateKey = $multisigPrivateKey;
	}

	public function toDTO() {
		$dto = array(
			'transaction' =>
				array(
					'timeStamp'  => $this->nemSDK->models()->transaction()->timeWindow()->timestamp(),
					'fee'        => $this->nemSDK->models()->fee()->multisig(),
					'type'       => $this->nemSDK->models()->transaction()->transactionsTypes()::MULTISIG,
					'deadline'   => $this->nemSDK->models()->transaction()->timeWindow()->deadline(),
					'version'    => $this->nemSDK->network()->getVersion(),
					'signer'     => $this->multisigPublicKey,
					'otherTrans' => $this->otherTrans,
					'signatures' =>
						array(),
				),
			'privateKey'  => $this->multisigPrivateKey,
		);

		return $dto;
	}

	/**
	 * @param mixed $otherTrans
	 */
	public function setOtherTrans( $otherTrans ) {
		$this->otherTrans = $otherTrans['transaction'];
	}

}