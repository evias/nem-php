<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Models;

/**
 * This is the MultisigModification class
 *
 * This class extends the NEM\Models\Model class
 * to provide with an integration of NEM's multisig 
 * modification objects.
 * 
 * @link https://nemproject.github.io/#multisigAggregateModificationTransaction
 */
class MultisigModification
    extends Model
{
    /**
     * NIS Multisig modification Types
     * 
     * @link https://bob.nem.ninja/docs/#multisigCosignatoryModification
     * @internal
     * @var integer
     */
    const TYPE_ADD = 1;
    const TYPE_REMOVE = 2;

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "modificationType",
        "cosignatoryAccount" // Public Key
    ];

    /**
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "modificationType" => "int",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "cosignatoryAccount",
    ];

    /**
     * MultisigModification DTO automatically builds a *NIS compliant*
     * [MultisigCosignatoryModification](https://bob.nem.ninja/docs/#multisigCosignatoryModification)
     *
     * @return  array       Associative array with key `modificationType` integer and `cosignatoryAccount` public key.
     */
    public function toDTO($filterByKey = null)
    {
        if (! in_array($this->modificationType, [self::TYPE_ADD, self::TYPE_REMOVE]))
            $this->modificationType = self::TYPE_ADD;

        return [
            "modificationType" => (int) $this->modificationType,
            "cosignatoryAccount" => $this->cosignatoryAccount()->publicKey,
        ];
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *mosaicId* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $nisData = $this->toDTO();

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];

        // serialize specialized fields
        $uint8_struct = $serializer->serializeInt(40); // length of structure
        $uint8_type   = $serializer->serializeInt($nisData["modificationType"]);
        $uint8_acct   = $serializer->serializeString(hex2bin($nisData["cosignatoryAccount"]));

        // concatenate uint8 representations
        $output = array_merge($uint8_struct, $uint8_type, $uint8_acct);
        return $output;
    }

    /**
     * Mutator for the cosignatoryAccount Account object.
     *
     * @return \NEM\Models\Account
     */
    public function cosignatoryAccount($publicKey = null)
    {
        return new Account(["publicKey" => $publicKey ?: $this->getAttribute("cosignatoryAccount")]);
    }
}
