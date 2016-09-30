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


/** \brief Helper class to store RGB values.
 *
 * \details This little helper class stores RGB values and evaluates all data,
 *  so only valid data can be stored.
 */
class color {
	public $r = 0; ///< Color red value.
	public $g = 0; ///< Color green value.
	public $b = 0; ///< Color blue value.
	public $a = 255; ///< Color alpha channel value.


	/** \brief Convert a RGB hex string to a \ref color object.
	 *
	 *
	 * \param src The string to be converted.
	 *
	 * \return Reference to this object.
	 */
	public function convert(string $src)
	{
		if (sscanf($src, '%02x%02x%02x', $this->r, $this->g, $this->b) != 3)
			throw new InvalidArgumentException(
				'Parameter must be 6 digit lowercase hex.');

		return $this;
	}


	/** \brief Return RGBA as RGB value.
	 *
	 *
	 * \return RGB hex string.
	 */
	function toRGB()
	{
		return sprintf('%02x%02x%02x', ($this->r * $this->a) / 255,
		 	($this->g * $this->a) / 255, ($this->b * $this->a) / 255);
	}
}

?>
