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

use ArrayAccess;


/** \brief Abstract class implementing the array modifier methods.
 *
 * \details Some classes need to be accessed like arrays, but are readonly. This
 *  abstract class implements the array modifier methods as stubs, so every
 *  class inherited from this class has to provide the read-methods only.
 */
abstract class ro_array implements ArrayAccess
{
	/** \brief Assigns a value to the specified offset.
	 *
	 * \note As the implemented array is read-only, this function does nothing.
	 *
	 *
	 * \param offset The offset to assign the value to.
	 * \param value The value to set.
	 */
	public function offsetSet($offset, $value)
	{
	}


	/** \brief Unsets an offset.
	 *
	 * \note As the implemented array is read-only, this function does nothing.
	 *
	 *
	 * \param offset The offset to unset.
	 */
	public function offsetUnset($offset)
	{
	}
}

?>
