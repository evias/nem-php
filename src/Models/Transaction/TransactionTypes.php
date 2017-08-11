<?php


namespace evias\NEMBlockchain\Models\Transaction;


class TransactionTypes {

	/**
	 * @internal
	 * @type {number}
	 */
	public const TRANSFER_TYPE = 0x0100;
	/**
	 * @internal
	 * @type {number}
	 */
	public const ASSET_TYPE = 0x0200;
	/**
	 * @internal
	 * @type {number}
	 */
	public const SNAPSHOT_TYPE = 0x0400;
	/**
	 * @internal
	 * @type {number}
	 */
	public const IMPORTANCE_TYPE = 0x0800;
	/**
	 * @internal
	 * @type {number}
	 */
	public const MULTISIG_TYPE = 0x1000;
	/**
	 * @internal
	 * @type {number}
	 */
	public const NAMESPACE_TYPE = 0x2000;
	/**
	 * @internal
	 * @type {number}
	 */
	public const MOSAIC_TYPE = 0x4000;
	/**
	 * Transfer Transaction
	 * @type {number}
	 */
	public const TRANSFER = self::TRANSFER_TYPE | 0x01;
	/**
	 * Importance transfer transaction.
	 * @type {number}
	 */
	public const IMPORTANCE_TRANSFER = self::IMPORTANCE_TYPE | 0x01;
	/**
	 * A new asset transaction.
	 * @type {number}
	 */
	public const ASSET_NEW = self::ASSET_TYPE | 0x01;
	/**
	 * An asset ask transaction.
	 * @type {number}
	 */
	public const ASSET_ASK = self::ASSET_TYPE | 0x02;
	/**
	 * An asset bid transaction.
	 * @type {number}
	 */
	public const ASSET_BID = self::ASSET_TYPE | 0x03;
	/**
	 * A snapshot transaction.
	 * @type {number}
	 */
	public const SNAPSHOT = self::SNAPSHOT_TYPE | 0x01;
	/**
	 * A multisig change transaction (e.g. announce an account as multi-sig).
	 * @type {number}
	 */
	public const MULTISIG_AGGREGATE_MODIFICATION = self::MULTISIG_TYPE | 0x01;
	/**
	 * A multisig signature transaction.
	 * @type {number}
	 */
	public const MULTISIG_SIGNATURE = self::MULTISIG_TYPE | 0x02;
	/**
	 * A multisig transaction.
	 * @type {number}
	 */
	public const MULTISIG = self::MULTISIG_TYPE | 0x04;
	/**
	 * A provision namespace transaction.
	 * @type {number}
	 */
	public const PROVISION_NAMESPACE = self::NAMESPACE_TYPE | 0x01;
	/**
	 * A mosaic definition creation transaction.
	 * @type {number}
	 */
	public const MOSAIC_DEFINITION_CREATION = self::MOSAIC_TYPE | 0x01;
	/**
	 * A mosaic supply change transaction.
	 * @type {number}
	 */
	public const MOSAIC_SUPPLY_CHANGE = self::MOSAIC_TYPE | 0x02;

}