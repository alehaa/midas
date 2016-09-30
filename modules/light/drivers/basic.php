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

use MIDAS\module\light\color;
use MIDAS\module\light\configurable;
use MIDAS\module\light\driver\LightDriverInterface;
use Pimple\Container;
use RuntimeException;


/* \brief Basic driver class.
 *
 * \details This driver will be used for basic LED controllers, which accept RGB
 * hex values via UART or network only. The controllers have no special
 * capabilities, so all the rendering for transitions etc. have to be done by
 * the driver.
 */
abstract class basic extends configurable implements LightDriverInterface {
	protected $app; ///< Copy of the calling app instance.

	protected $id; ///< The lamp's ID.
	protected $file; ///< The lamp's device file.
	protected $transition = 0; ///< Time to use for transitions for this lamp.


	/** \brief Constructor.
	 *
	 *
	 * \param app The attached Pimple container.
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}


	/** \brief Check if \p src contains all required keys listed in \p required.
	 *
	 *
	 * \param src The array to be checked.
	 * \param required Array of required keys.
	 *
	 * \exception RuntimeException A required key was not found.
	 */
	private function check_required_keys(array $src, array $required)
	{
		foreach ($required as $key)
			if (!isset($src[$key]))
				throw new RuntimeException('Missing required key '.$key);
	}


	/** \brief Configure the device.
	 *
	 *
	 * \param config The device configuration array.
	 */
	public function configure(array $config)
	{
		$this->check_required_keys($config, array('id', 'file', 'transition'));

		$this->id = $config['id'];
		$this->file = $config['file'];
		$this->transition = $config['transition'];

		$this->configured = true;
	}
}


?>
