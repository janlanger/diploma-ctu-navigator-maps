<?php

namespace Kdyby;

use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class PresenterInfo extends Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $module;

	/** @var string */
	private $class;

	/** @var array */
	private $actions;

	/** @var Kdyby\PresenterTree */
	private $tree;



	/**
	 * @param string $name
	 * @param string $module
	 * @param string $class
	 */
	public function __construct($name, $module, $class)
	{
		$this->name = $name;
		$this->module = $module;
		$this->class = $class;
	}



	/**
	 * @return bool
	 */
	public function isPublic()
	{
		$ref = $this->getPresenterReflection();
		return !$ref->hasAnnotation('hideInTree');
	}



	/**
	 * @return ClassType
	 */
	public function getPresenterReflection()
	{
		return ClassType::from($this->getPresenterClass());
	}



	/**
	 * @return array
	 */
	public function getActions()
	{
		if ($this->actions === NULL) {
			$this->actions = $this->getTree()->getPresenterActions($this);
		}

		return $this->actions;
	}



	/**
	 * @return string
	 */
	public function getName($full = FALSE)
	{
		return ($full ? ':' . $this->module . ':' : NULL) . $this->name;
	}



	/**
	 * @return string
	 */
	public function getPresenterClass()
	{
		return $this->class;
	}



	/**
	 * @return string
	 */
	public function getModule()
	{
		return $this->module;
	}



	public function __toString()
	{
		return $this->getName(TRUE);
	}



	/**
	 * @return Kdyby\PresenterTree
	 */
	private function getTree()
	{
		if ($this->tree === NULL) {
			$this->tree = $this->getContext()->getService("presenterTree");
		}

		return $this->tree;
	}



	/**
	 * @return Nette\context
	 */
	private function getContext()
	{
		return Nette\Environment::getContext();
	}



	public function __sleep()
	{
		$properties = (array)$this;
		unset($properties['tree'], $properties['actions']);
		return array_keys($properties);
	}

}
