<?php

/* This file is part of MIDAS.
 *
 * MIDAS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MIDAS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see
 *
 *  http://www.gnu.org/licenses/
 *
 *
 * Copyright (C)
 *  2016 Alexander Haase <ahaase@alexhaase.de>
 */

namespace MIDAS;

use MIDAS\ApiProviderInterface;
use MIDAS\config;
use SilMod\SilMod;


require_once 'config.php';


class MIDAS extends SilMod
{
	/** \brief Constructor.
	 *
	 * \details Creates a new SilMod object and initializes all modules.
	 *
	 *
	 * \param options Option array.
	 */
	public function __construct(array $options = array())
	{
		/* Initialize SilMod and register all required frameworks for the basic
		 * MIDAS infrastructure. */
		parent::__construct($options);

		if (!isset($options['theme']))
			$options['theme'] = 'default';
		$this['twig.loader.filesystem']->prependPath(
			'themes/'.$options['theme'].'/views');

		$this->register(new config(isset($options['config']['path']) ?
			$this->toArray($options['config']['path']) : array()));
	}


	/** \brief Mount API controller.
	 *
	 * \details This method is a tiny wrapper around Silex 'mount' function.
	 *  Instead of mounting the returned value of 'connect' of \p obj under \p
	 *  name, this function mounts the return value of 'connect_api' under
	 *  'api/<name>'.
	 *
	 *
	 * \param name Mount point.
	 * \param obj Object to mount.
	 */
	public function mount_api($name, ApiProviderInterface $class)
	{
		$this->mount('/api'.$name, $class->connect_api($this));
	}
};

?>
