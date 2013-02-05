<?php


namespace Maps\Model\Persistence;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface IObjectFactory
{

	/**
	 * @return object
	 */
	function createNew($arguments = array());

}