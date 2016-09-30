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

namespace MIDAS\module\light;

use LogicException;


/** \brief Abstract helper class for configurable helper classes.
 *
 * \details When using Pimple as dependency manager, you may not pass parameters
 *  to the constructor. Instead a separate function may be used to configure the
 *  class. This abstract class provides the basic unfrastructure for such
 *  classes.
 */
abstract class configurable {
	protected $configured = false; ///< Configuration state.


	/** \brief Check if object is configured.
	 *
	 *
	 * \exception RuntimeException The object is not configured.
	 */
	public function is_configured()
	{
		if (!$this->configured)
			throw new LogicException('Object is not configured.');
	}
}

?>
