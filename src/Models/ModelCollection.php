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
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Models;

use \Illuminate\Support\Collection;
use \Illuminate\Support\Arr as ArrayHelper;
use \NEM\Infrastructure\ServiceInterface;
use BadMethodCallException;

class ModelCollection
    extends Collection
{
    /**
     * Overwrite toArray() functionality to make sure it will always use 
     * Data Transfer Objects when the collection is cast into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toDTO();
    }

    /**
     * Generic helper to convert a Collection instance to a Data Transfer Object (array).
     *
     * This will make it easy to bridge implemented models to NEM *NIS compliant*
     * objects.
     *
     * @see http://bob.nem.ninja/docs/  NIS API Documentation
     * @return  array       Array representation of the collection objects *compliable* with NIS definition.
     */
    public function toDTO() 
    {
        $dtos = [];
        foreach ($this->all() as $ix => $item) {
            if ($item instanceof ModelInterface)
                array_push($dtos, $item->toDTO());
            else
                array_push($dtos, $item);
        }

        return $dtos;
    }

    /**
     * Helper to *paginate requests* to NIS.
     *
     * This will automatically re-call the Service method in case there
     * is more results to process.
     *
     * This method can be used to return the *full list* of transactions
     * rather than just a *pageSize* number of transactions.
     *
     * @param   \NEM\Infrastructure\ServiceInterface    $service    The NEM Service helper.
     * @param   string                                  $method     The Service method name to replicate.
     * @param   array                                   $arguments  The (optional) arguments list for the forwarded method call.
     * @param   string                                  $field      The (optional) Array Dot Notation for retrieving the *counting* field.
     * @param   integer                                 $pageSize   The (optional) number of elements which NIS will return with the given Service method.
     * @return  \NEM\Models\ModelCollection
     * @throws  \BadMethodCallException         On invalid service method.
     */
    public function paginate(ServiceInterface $service, $method, array $arguments = [], $field = "id", $pageSize = 25)
    {
        if (! method_exists($service, $method)) {
            throw new BadMethodCallException("Invalid method name '" . $method . "'. Not implemented in Service '" . get_class($service) . "'.");
        }

        $hasValues = [];
        $objects = [];
        $cntResults = 0;
        do {
            // forward service method call
            $items = call_user_func_array([$service, $method], $arguments);
            $cntResults = count($objects);

            $lastObj = end($objects);
            $dotObj  = ArrayHelper::dot((array) $lastObj);

            $lastValue = $dotObj[$field];

            if (in_array($lastValue, $hasValues))
                break; // done (looping)

            if ($cntResults < $pageSize)
                break; // done (retrieved less values than maximum possible)

            array_push($hasValues, $lastValue);
            $objects = $objects + $items;
        }
        while ($cntResults);

        return $objects;
    }
}
