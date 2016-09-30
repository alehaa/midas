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

namespace MIDAS\module\light\driver;

use Pimple\Container;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/** \brief Helper class to select the driver for a lamp.
 *
 * \details This little helper class converts a given ID (the parameter of a
 *  Silex route) to a configured driver object. The configuration for the lamp
 *  will be looked up and passed to the driver, so the driver may be used out
 *  of the box by the router.
 */
class selector {
	private $app; ///< Copy of the calling app instance.


	/** \brief Constructor.
	 *
	 *
	 * \param app The attached Pimple container.
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}


	/** \brief Return the driver for lamp \p id.
	 *
	 * \details This function looks up the configuration for lamp \p id, selects
	 *  the required driver and returns an initialized and configured driver
	 *  object.
	 *
	 *
	 * \param id The lamps ID.
	 *
	 * \return The driver object.
	 */
	public function select(int $id)
	{
		if (!isset($this->app['config']['light'][$id]))
			throw new NotFoundHttpException('Lamp '.$id.' does not exist');

		$config = $this->app['config']['light'][$id];

		if (!isset($config['driver']))
			throw new RuntimeException('No driver set for lamp '.$id);

		$driver = $this->app['modules.light.drivers.'.$config['driver']];
		$driver->configure($config);

		return $driver;
	}
}

?>
