<?php

namespace DAC;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\ORMException;

abstract class Base {
	private $_meta;
	protected static $_EM;

	/**
	 * _EM
	 *
	 * Gets or Sets the Entity Manager
	 *
	 * @param EntityManager|null $EM (optional) Entity Manager
	 * @return EntityManager Entity Manager
	 */
	public static function _EM(?EntityManager $EM = null) {
		if($EM != null)
			self::$_EM = $EM;
		return self::$_EM;
	}

	/**
	 * __construct
	 *
	 * Constructor
	 */
	public function __construct() {
		$this->_meta = self::_EM()->getClassMetadata(get_class($this));
		foreach($this->_meta->associationMappings as $property => $data) {
			if(($this->$property === null) && ($data['type'] & ClassMetadataInfo::TO_MANY)) // OneToMany or ManyToMany
				$this->$property = new ArrayCollection();
		}
	}

	/**
	 * FindBy
	 *
	 * Searches the Repository for objects
	 *
	 * @param array $params (optional) Search parameters
	 * @param array|null $order (optional) Results order
	 * @param null $limit (optional) Results limit
	 * @param null $offset (optional) Results offset
	 * @return static[]|false Array of objects found, or false on query error
	 */
	public static function FindBy($params = array(), ?array $order = null, $limit = null, $offset = null) {
		return self::_EM()->getRepository(get_called_class())->findBy($params, $order, $limit, $offset);
	}

	/**
	 * FindOneBy
	 *
	 * Searches the Repository for a single object by a unique field
	 *
	 * @param array $params Search parameters
	 * @return static|object|null|false Object found, null if not found or false on query error
	 */
	public static function FindOneBy(array $params) {
		return self::_EM()->getRepository(get_called_class())->findOneBy($params);
	}

	/**
	 * GDE
	 *
	 * Converte dado DAC para dado GDE
	 *
	 * @param bool $load Se deve carregar do DB
	 * @param \GDE\Base|null $Parent
	 * @param bool $dirty Se o objeto foi modificado
	 * @return \GDE\Base|null
	 */
	public abstract function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base;

	/**
	 * __call
	 *
	 * Automagically handle some generic method calls
	 *
	 * @param $name
	 * @param $args
	 * @return mixed
	 */
	public function __call($name, $args) {
		if(preg_match('/^(get|set)(.+?)$/i', $name, $matches) > 0) {
			if($this->_meta === null)
				$this->_meta = self::_EM()->getClassMetadata(get_class($this));
			list($full, $method, $property) = $matches;
			$property = strtolower($property);
			$type = (!empty($this->_meta->fieldMappings[$property]['type']))
				? $this->_meta->fieldMappings[$property]['type']
				: null;
			if(property_exists($this, $property) === false)
				throw new \InvalidArgumentException('Property '.$property.' not found on class '.get_class($this).'.');
			$_association = (isset($this->_meta->associationMappings[$property]))
				? $this->_meta->associationMappings[$property]
				: false;
			switch($method) { // Method type
				case 'get': // GET
					if(($type === null) && ($_association === false)) // Non-doctrine property
						return $this->{$property};
					if($_association !== false) { // Is Association
						$res = $this->{$property};
						if($res === null) { // Empty
							switch($_association['type']) {
								case ClassMetadataInfo::ONE_TO_ONE: // OneToOne (mappedBy)
								case ClassMetadataInfo::MANY_TO_ONE: // OneToOne / ManyToOne (inversedBy)
									return $res;
								case ClassMetadataInfo::ONE_TO_MANY: // OneToMany
								case ClassMetadataInfo::MANY_TO_MANY: // ManyToMany
									return $this->{$property} = new ArrayCollection();
								default:
									die('Base: '.get_class($this).'->'.$property.': '.$_association['type']);
							}
						} else
							return $res;
					} else { // Scalar Property
						if($this->{$property} === null)
							return null;
						return ((isset($args[0])) && ($args[0] === true)) ? htmlspecialchars($this->{$property}) : $this->{$property};
					}
					break;
				case 'set': // SET
					if(($type === null) && ($_association === false)) // Non-doctrine property
						return $this->{$property} = (array_key_exists(0, $args)) ? $args[0] : null;
					if(!array_key_exists(0, $args)) { // No value passed
						throw new \InvalidArgumentException("Can't set ".$property." without a value on ".get_class($this).'.');
					} else { // Value passed
						$value = $args[0];
						$set_other_side = ((!isset($args[1])) || ($args[1] === true));
						if($_association !== false) { // Is Association
							// Convert value from array to ArrayCollection
							if(($_association['type'] & ClassMetadataInfo::TO_MANY) && (is_array($value)))
								$value = new ArrayCollection($value);
							switch($_association['type']) {
								case ClassMetadataInfo::ONE_TO_ONE: // OneToOne
									if((!is_object($value)) && (!is_null($value)))
										throw new \InvalidArgumentException("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation, if needed
									if(($set_other_side === true) && (is_object($value))) {
										if(!empty($_association['mappedBy']))
											$value->{$_association['mappedBy']} = $this;
										if(!empty($_association['inversedBy']))
											$value->{$_association['inversedBy']} = $this;
									}
									break;
								case ClassMetadataInfo::ONE_TO_MANY: // OneToMany
									if((is_array($value) === false) && ((!is_object($value)) || (!($value instanceof ArrayCollection))))
										throw new \InvalidArgumentException("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation for every object in the array
									if(($set_other_side === true) && (!empty($_association['mappedBy']))) {
										foreach($value as $object)
											if(is_object($object))
												$object->{$_association['mappedBy']} = $this;
									}
									// Convert value from array to ArrayCollection
									if(is_array($value))
										$value = new ArrayCollection($value);
									break;
								case ClassMetadataInfo::MANY_TO_ONE: // ManyToOne
									if((!is_object($value)) && (!is_null($value)))
										throw new \InvalidArgumentException("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation for every object in the array
									if(($set_other_side === true) && (!(empty($_association['inversedBy']))) && (is_object($value) && (is_object($value->{$_association['inversedBy']}))))
										if($value->{$_association['inversedBy']}->contains($this) === false)
											$value->{$_association['inversedBy']}->add($this);
									break;
								case ClassMetadataInfo::MANY_TO_MANY: // ManyToMany
									if((is_array($value) === false) && ((!is_object($value)) || (!($value instanceof ArrayCollection))))
										throw new \InvalidArgumentException("Invalid argument type passed to ".$name." on ".get_class($this).': '.gettype($value));
									// Determine if this is the inverse side and set the inverse relation for every object in the array
									if(($set_other_side === true) && (!empty($_association['mappedBy']))) {
										foreach($value as $object) {
											if((is_object($object)) && (!empty($object->{$_association['mappedBy']})) && ($object->{$_association['mappedBy']}->contains($this) === false))
												$object->{$_association['mappedBy']}->add($this);
										}
									}
									break;
							}
						} else { // Scalar Property
							if($value === '') // Convert empty string into null
								$value = null;
						}
					}
					if((!isset($old)) || (!isset($new)) || ($old != $new))
						return $this->{$property} = $value;
					else
						return $value;
					break;
				default:
					throw new \InvalidArgumentException("Method ".$name." not found on class ".get_class($this).'.');
			}
		} else {
			throw new \InvalidArgumentException("Method ".$name." not found on class ".get_class($this).'.');
		}
	}

}
