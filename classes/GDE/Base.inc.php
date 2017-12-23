<?php

namespace GDE;

/**
 * Base class with Timezone support
 *
 * @author Guaycuru
 */

abstract class Base {
	// EM
	private $_meta;
	protected static $_EM;
	protected static $_LOGs = array();
	private static $_trans_count = 0;
	protected static $_loggable = true;
	
	// Display Timezone
	protected static $_TZ = null;
	
	/** 
	 * _EM
	 *
	 * Gets or Sets the Entity Manager
	 *
	 * @param object $EM (optional) Entity Manager
	 * @return \Doctrine\ORM\EntityManager Entity Manager
	 */
	public static function _EM($EM = null) {
		if($EM != null)
			self::$_EM = $EM;
		return self::$_EM;
	}
	
	/** 
	 * _TZ
	 *
	 * Gets or Sets the Display Timezone
	 *
	 * @param DateTimeZone|string $Timezone (optional) Timezone
	 * @return DateTimeZone|null Current display Timezone
	 */
	public static function _TZ($Timezone = null) {
		if($Timezone !== null) {
			if(is_object($Timezone) === false)
				$Timezone = new \DateTimeZone($Timezone);
			self::$_TZ = $Timezone;
		}
		return self::$_TZ;
	}

	/**
	 * __construct
	 *
	 * Constructor
	 */
	public function __construct() {
		$this->_meta = self::_EM()->getClassMetadata(get_class($this));
		foreach($this->_meta->associationMappings as $property => $data) {
			if(($this->$property === null) && ($data['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY)) // OneToMany or ManyToMany
				$this->$property = new \Doctrine\Common\Collections\ArrayCollection();
		}
	}
	
	/** 
	 * To_JSON
	 *
	 * Returns the input in JSON
	 *
	 * @param mixed $input The input oO
	 * @return string The JSON encoded output
	 */
	public static function To_JSON($input) {
		return json_encode($input, JSON_FORCE_OBJECT & JSON_NUMERIC_CHECK);
	}
	
	/** 
	 * OK_JSON
	 *
	 * Outputs a JSON OK and terminates the execution
	 *
	 * @param integer $id Object ID
	 * @param integer $http (Optional) HTTP response code
	 * @param array $extra (Optional) Extra info to be sent with the response JSON
	 * @return void
	 */
	public static function OK_JSON($id = null, $http = 200, $extra = array()) {
		http_response_code($http);
		die(self::To_JSON(array(
			'ok' => true,
			'id' => $id
		) + $extra));
	}
	
	/** 
	 * Erro_JSON
	 *
	 * Outputs a JSON error and terminates the execution
	 *
	 * @param string $message Error message
	 * @param integer $http (Optional) HTTP response code
	 * @param array $extra (Optional) Extra info to be sent with the response JSON
	 * @return void
	 */
	public static function Error_JSON($message, $http = 200, $extra = array()) {
		http_response_code($http);
		die(self::To_JSON(array(
			'ok' => false,
			'error' => $message
		) + $extra));
	}

	/**
	 * Save_JSON
	 *
	 * Saves and outputs JSON
	 *
	 * @param bool $flush
	 * @param array $extra
	 * @return void
	 */
	public function Save_JSON($flush = true, $extra = array()) {
		if($this->Save($flush) === true) {
			if(is_callable($extra))
				$extra = $extra();
			self::OK_JSON($this->getID(), 200, $extra);
		} else
			self::Error_JSON('Um erro desconhecido ocorreu, por favor tente novamente.');
	}

	/**
	 * Delete_JSON
	 *
	 * Deletes and outputs JSON
	 *
	 * @param bool $flush
	 * @return void
	 */
	public function Delete_JSON($flush = true) {
		if($this->Delete($flush) === true)
			self::OK_JSON();
		else
			self::Error_JSON('Um erro desconhecido ocorreu, por favor tente novamente.');
	}

	/**
	 * Required_Fields
	 *
	 * @param $campos
	 * @return bool
	 */
	public static function Required_Fields($campos) {
		foreach($campos as $campo)
			if((!isset($_POST[$campo])) || ($_POST[$campo] === ''))
				return false;
		return true;
	}
	
	/** 
	 * Download_Headers
	 *
	 * Outputs the HTTP headers for downloading a file
	 *
	 * @param string $filename The file name
	 * @param integer $filesize The file size (in bytes)
	 * @param string $mime (Optional) The mime type
	 * @return void
	 */
	public static function Download_Headers($filename, $filesize, $mime = 'application/octet-stream') {
		header("Content-type: ".$mime);
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Length: ".$filesize);
		header("Content-Transfer-Encoding: binary");
		// Avoid cache
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
		header('Last-Modified: '.gmdate('D, d M Y H:i:s' ).' GMT'); 
		header('Cache-Control: no-store, no-cache, must-revalidate'); 
		header('Cache-Control: post-check=0, pre-check=0', false); 
		header('Pragma: no-cache'); 
	}
	
	/** 
	 * FindBy
	 *
	 * Searches the DB for objects
	 *
	 * @param array $params (optional) Search parameters
	 * @param array $order (optional) Results order
	 * @param integer $limit (optional) Results limit
	 * @param integer $offset (optional) Results offset
	 * @return array|false Array of objects found, or false on query error
	 */
	public static function FindBy($params = array(), array $order = null, $limit = null, $offset = null) {
		return self::_EM()->getRepository(get_called_class())->findBy($params, $order, $limit, $offset);
	}
	
	/** 
	 * FindOneBy
	 *
	 * Searches the DB for only one object
	 *
	 * @param array $params Search parameters
	 * @return object|null|false Object found, null if not found or false on query error
	 */
	public static function FindOneBy($params) {
		return self::_EM()->getRepository(get_called_class())->findOneBy($params);
	}
	
	/** 
	 * Load
	 *
	 * Loads the object
	 *
	 * @param mixed $id The ID to load into the object
	 * @return static This object
	 */
	public static function Load($id = null) {
		if($id == null) { // No data, new object
			return new static();
		}
		$Obj = self::_EM()->find(get_called_class(), $id);
		return ($Obj !== null) ? $Obj : new static();
	}
	
	/** 
	 * StartTrans
	 *
	 * Starts a Transaction block -> Should be avoided because LOGs may not be saved if the trans fails!
	 *
	 */
	public static function StartTrans() {
		self::$_trans_count++;
		return self::_EM()->getConnection()->beginTransaction();
	}
	
	/** 
	 * CompleteTrans
	 *
	 * Completes a Transaction block
	 *
	 * @param boolean $commit True to commit, false to rollback
	 * @return boolean True if the transaction was committed, false otherwise
	 */
	public static function CompleteTrans($commit = true) {
		self::$_trans_count--;
		
		// Finish the transaction
		if($commit) {
			self::_EM()->flush();
			self::_EM()->getConnection()->commit();
			return true;
		} else {
			self::_EM()->getConnection()->rollback();
		}
		
		// Check if there are any LOGs that need to be flushed
		if(count(self::$_LOGs) > 0) {
			foreach(self::$_LOGs as $Log) {
				self::_EM()->persist($Log);
				self::_EM()->flush($Log);
			}
		}
		
		return $commit;
	}
	
	/**
	 * TransCount
	 * 
	 * Returns the number os transactions currently open
	 * 
	 * @return integer Number of transactions currently open
	 */
	public static function TransCount() {
		return self::$_trans_count;
	}
	
	public static function Loggable() {
		return static::$_loggable;
	}
	
	/** 
	 * getID
	 *
	 * Returns the object primary key's value
	 *
	 * @return integer This object primary key's value
	 */
	public function getID() {
		if($this->_meta === null)
			$this->_meta = self::_EM()->getClassMetadata(get_class($this));
		$identifier = $this->_meta->getSingleIdentifierFieldName();
		return $this->{$identifier};
	}
	
	/** 
	 * Save
	 *
	 * Saves the object properties to the DB
	 *
	 * @param boolean $flush (optional) Whether to write the changes to the DB
	 * @return boolean True in case of success or false in case of error
	 */
	public function Save($flush = true) {
		if(self::_EM()->persist($this) === false)
			return false;
		
		if(($flush) && (self::_EM()->flush() === false))
			return false;
		
		return true;
	}
	
	/** 
	 * Delete
	 *
	 * Deletes the object properties from the DB
	 *
	 * @param boolean $flush (optional) Whether to write the changes to the DB
	 * @return boolean True in case of success or false in case of error
	 */
	public function Delete($flush = true) {
		if($this->getID() == null)
			return true;

		// Delete data from the DB
		self::_EM()->remove($this);
		if($flush)
			self::_EM()->flush();
		
		return true;
	}

	/**
	 * __call
	 *
	 * handles GET's and SET's
	 *
	 * @param $name
	 * @param $args
	 * @return mixed O valor da propriedade
	 * @throws \Exception
	 */
	public function __call($name, $args) {
		if(preg_match('/^(get|set|add|remove|clear|has)(.+?)$/i', $name, $matches) > 0) {
			if($this->_meta === null)
				$this->_meta = self::_EM()->getClassMetadata(get_class($this));
			list($full, $method, $property) = $matches;
			$property = strtolower($property);
			$type = (!empty($this->_meta->fieldMappings[$property]['type']))
				? $this->_meta->fieldMappings[$property]['type']
				: null;
			if(property_exists($this, $property) === false)
				throw new \Exception('Property '.$property.' not found on class '.get_class($this).'.');
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
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE: // OneToOne (mappedBy)
									if((isset($args[0])) && ($args[0] === true)) {
										// Create a new object
										$New = new $_association['targetEntity']();
										// Set the inverse relation
										// ToDo: Determine if this is really necessary, as it can cause a Doctrine Exception (entity not configured to cascade)
										if(!empty($_association['mappedBy']))
											$New->{'set'.$_association['mappedBy']}($this);
										return $this->{$property} = $New;
									} else
										return $res;
									break;
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE: // OneToOne / ManyToOne (inversedBy)
									if((isset($args[0])) && ($args[0] === true)) {
										// Create a new object
										$New = new $_association['targetEntity']();
										// Set the inverse relation
										// ToDo: Determine if this is really necessary, as it can cause a Doctrine Exception (entity not configured to cascade)
										if(!empty($_association['inversedBy'])) { // It's inversed by
											$inversed = $_association['inversedBy'];
											$there = self::_EM()->getClassMetadata($_association['targetEntity'])->associationMappings[$inversed]['type'];
											if($there == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE) // The other side is an One-To-One
												$New->{'set'.$inversed}($this);
											elseif($there == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY) // The other side is an One-To-Many
												$New->{'get'.$inversed}()->add($this);
										}
										return $this->{$property} = $New;
									} else
										return $res;
									break;
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY: // OneToMany
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY: // ManyToMany
									return $this->{$property} = new \Doctrine\Common\Collections\ArrayCollection();
									break;
								default:
									die('Base: '.get_class($this).'->'.$property.': '.$_association['type']);
							}
						} else
							return $res;
					} else { // Scalar Property
						if($this->{$property} === null)
							return null;
						switch($type) {
							case 'date':
							case 'datetime':
							case 'time':
								// Check if we need to apply the display Timezone
								if((isset($args[1])) && ($args[1] != date_default_timezone_get())) { // Display timezone provided as argument
									$datetime = clone $this->{$property};
									$timezone = ($args[1] instanceof \DateTimeZone)
										? $args[1]
										: new \DateTimeZone($args[1]);
									$datetime->setTimezone($timezone);
								} else if((self::_TZ() !== null) && (self::_TZ()->getName() != date_default_timezone_get())) {
									$datetime = clone $this->{$property};
									$datetime->setTimezone(self::_TZ());
								} else
									$datetime = $this->{$property};
								if((!isset($args[0])) || ($args[0] === false)) // Return DateTime object
									return $datetime;
								if((isset($args[0])) && (is_string($args[0])))
									$format = $args[0];
								elseif($type == 'datetime')
									$format = 'Y-m-d H:i:s';
								elseif($type == 'date')
									$format = 'Y-m-d';
								else
									$format = 'H:i:s';
								return $datetime->format($format);
							default:
								return ((isset($args[0])) && ($args[0] === true)) ? htmlspecialchars($this->{$property}) : $this->{$property};
						}
					}
					break;
				case 'set': // SET
					if(($type === null) && ($_association === false)) // Non-doctrine property
						return $this->{$property} = (array_key_exists(0, $args)) ? $args[0] : null;
					if(!array_key_exists(0, $args)) { // No value passed
						switch($type) {
							case 'date':
								$value = new \DateTime();
								$value->setTime(0, 0, 0);
								break;
							case 'datetime':
								$value = new \DateTime();
								break;
							default:
								throw new \Exception("Can't set ".$property." without a value on ".get_class($this).'.');
						}
					} else { // Value passed
						$value = $args[0];
						// Convert value from array to ArrayCollection
						if(($_association['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY) && (is_array($value)))
							$value = new \Doctrine\Common\Collections\ArrayCollection($value);
						$set_other_side = ((!isset($args[1])) || ($args[1] === true));
						if($_association !== false) { // Is Association
							switch($_association['type']) {
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE: // OneToOne
									if((!is_object($value)) && (!is_null($value)))
										throw new \Exception("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation, if needed
									if($set_other_side === true) {
										if((!empty($_association['mappedBy'])) && (is_object($value)))
											$value->{$_association['mappedBy']} = $this;
									}
									break;
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY: // OneToMany
									if((is_array($value) === false) && ((!is_object($value)) || (!($value instanceof \Doctrine\Common\Collections\ArrayCollection))))
										throw new \Exception("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation for every object in the array
									if($set_other_side === true) {
										if(!empty($_association['mappedBy'])) {
											foreach($value as $object)
												if(is_object($object))
													$object->{$_association['mappedBy']} = $this;
										}
									}
									// Convert value from array to ArrayCollection
									if(is_array($value))
										$value = new \Doctrine\Common\Collections\ArrayCollection($value);
									break;
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE: // ManyToOne
									if((!is_object($value)) && (!is_null($value)))
										throw new \Exception("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation for every object in the array
									if($set_other_side === true) {
										if(!(empty($_association['inversedBy'])) && (is_object($value)))
											if($value->{$_association['inversedBy']}->contains($this) === false)
												$value->{$_association['inversedBy']}->add($this);
									}
									break;
								case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY: // ManyToMany
									if((is_array($value) === false) && ((!is_object($value)) || (!($value instanceof \Doctrine\Common\Collections\ArrayCollection))))
										throw new \Exception("Invalid argument type passed to ".$name." on ".get_class($this).'.');
									// Determine if this is the inverse side and set the inverse relation for every object in the array
									if($set_other_side === true) {
										if(!empty($_association['mappedBy'])) {
											foreach($value as $object) {
												if((is_object($object)) && ($object->{$_association['mappedBy']}->contains($this) === false))
													$object->{$_association['mappedBy']}->add($this);
											}
										}
									}
									break;
							}
						} else { // Scalar Property
							if($value === '') // Convert empty string into null
								$value = null;
							elseif(isset($this->_meta->fieldMappings[$property])) {
								switch($type) {
									case 'date':
										$old = $this->{$property};
										if($old !== null)
											$old = $old->format('d/m/Y');
										if(is_string($value)) {
											if(!empty($args[1]))
												$format = $args[1];
											elseif(strpos($value, '/') !== false)
												$format = 'd/m/Y';
											else
												$format = 'Y-m-d';
											$value = \DateTime::createFromFormat($format, $value);
											$value->setTime(0, 0, 0);
										}
										if($value instanceof \DateTime)
											$new = $value->format('d/m/Y');
										else
											$value = $new = null;
										break;
									case 'datetime':
										$old = $this->{$property};
										if($old !== null)
											$old = $old->format('U');
										if(is_string($value)) {
											if(!empty($args[1]))
												$format = $args[1];
											elseif(strpos($value, '/') !== false)
												$format = 'd/m/Y H:i:s';
											else
												$format = 'Y-m-d H:i:s';
											$value = \DateTime::createFromFormat($format, $value);
											if($value === false)
												$value = null;
										}
										if($value instanceof \DateTime)
											$new = $value->format('U');
										else
											$value = $new = null;
										break;
									case 'time':
										$old = $this->{$property};
										if($old !== null)
											$old = $old->format('H:i:s');
										if(is_string($value)) {
											if(!empty($args[1]))
												$format = $args[1];
											else
												$format = 'H:i:s';
											$value = \DateTime::createFromFormat($format, $value);
										}
										if($value instanceof \DateTime)
											$new = $value->format('H:i:s');
										else
											$value = $new = null;
										break;
									default:
										break;
								}
							}
						}
					}
					if((!isset($old)) || (!isset($new)) || ($old != $new))
						return $this->{$property} = $value;
					else
						return $value;
					break;
				case 'add':
					if($_association['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_ONE)
						throw new \Exception("Can't add".$name."() for property TO_ONE on class ".get_class($this).'.');
					if(!array_key_exists(0, $args)) // No value passed
						throw new \Exception("No value passed for ".$name."() on class ".get_class($this).'.');
					$Obj = (is_object($args[0]))
						? $args[0]
						: $_association['targetEntity']::Load($args[0]);
					if(!($Obj instanceof $_association['targetEntity']))
						throw new \Exception("Object passed to ".$name."() is not an instance of ".$_association['targetEntity']." on class ".get_class($this).'.');
					if(!empty($_association['mappedBy'])) {
						if($_association['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY)
							$Obj->{$_association['mappedBy']}->add($this);
						else // OneToMany
							$Obj->{$_association['mappedBy']} = $this;
					}
					if(!empty($_association['inversedBy'])) // Always a ManyToMany
						$Obj->{$_association['inversedBy']}->add($this);
					if($this->{$property} === null)
						$this->{$property} = new \Doctrine\Common\Collections\ArrayCollection();
					return $this->{$property}->add($Obj);
					break;
				case 'remove':
					if($_association['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_ONE)
						throw new \Exception("Can't remove".$name."() for property TO_ONE on class ".get_class($this).'.');
					if(!array_key_exists(0, $args)) // No value passed
						throw new \Exception("No value passed for ".$name."() on class ".get_class($this).'.');
					$Obj = (is_object($args[0]))
						? $args[0]
						: $_association['targetEntity']::Load($args[0]);
					if(!($Obj instanceof $_association['targetEntity']))
						throw new \Exception("Object passed to ".$name."() is not an instance of ".$_association['targetEntity']." on class ".get_class($this).'.');
					if(!empty($_association['mappedBy'])) {
						if($_association['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY)
							$Obj->{$_association['mappedBy']}->removeElement($this);
						else // OneToMany
							$Obj->{$_association['mappedBy']} = null;
					}
					if(!empty($_association['inversedBy'])) // Always a ManyToMany
						$Obj->{$_association['inversedBy']}->removeElement($this);
					if($this->{$property} === null)
						return false;
					return $this->{$property}->removeElement($Obj);
					break;
				case 'clear':
					if(!($_association['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY))
						throw new \Exception("Can't clear".$name."() for a not TO_MANY property on class ".get_class($this).'.');
					$clear_other_side = ((!isset($args[0])) || ($args[0] === true));
					if($clear_other_side) {
						if($_association['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY) {
							if(!empty($_association['mappedBy'])) {
								foreach($this->{$property} as $Obj)
									$Obj->{$_association['mappedBy']} = null;
							}
						} else { // ManyToMany
							if(!empty($_association['mappedBy'])) { // Inverse Side, clear the other side so Doctrine detects the change
								foreach($this->{$property} as $Obj)
									$Obj->{$_association['mappedBy']}->removeElement($this);
							} elseif(!empty($_association['inversedBy'])) { // Owning Side, clear the other side
								foreach($this->{$property} as $Obj)
									$Obj->{$_association['inversedBy']}->removeElement($this);
							}
						}
					}
					$this->{$property}->clear();
					break;
				case 'has':
					if(!($_association['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY))
						throw new \Exception("Can't has".$name."() for a not TO_MANY property on class ".get_class($this).'.');
					if(!array_key_exists(0, $args)) // No value passed
						throw new \Exception("No value passed for ".$name."() on class ".get_class($this).'.');
					if(is_object($args[0])) {
						if(!($args[0] instanceof $_association['targetEntity']))
							throw new \Exception("Object passed to ".$name."() is not an instance of ".$_association['targetEntity']." on class ".get_class($this).'.');
						else
							$id = $args[0]->getID();
					} else
						$id = $args[0];
					return $this->{$property}->exists(function($k, $O) use ($id) {
						return $O->getID() == $id;
					});
					break;
				default:
					throw new \Exception("Method ".$name." not found on class ".get_class($this).'.');
			}
		} else {
			throw new \Exception("Method ".$name." not found on class ".get_class($this).'.');
		}
	}
}
