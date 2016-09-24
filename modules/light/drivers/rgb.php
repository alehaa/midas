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

namespace MIDAS\light\driver\helper;


/** \brief Helper class to store RGB values.
 *
 * \details This little helper class stores RGB values and evaluates all data,
 *  so only valid data can be stored.
 */
class rgb {
	public $r;
	public $g;
	public $b;
	public $a;


	function __construct($r, $g, $b, $a = 255)
	{
		if ($r < 0 || $r > 255)
			throw new \RuntimeException("Red value is out of range.");

		if ($g < 0 || $g > 255)
			throw new \RuntimeException("Green value is out of range.");

		if ($b < 0 || $b > 255)
			throw new \RuntimeException("Blue value is out of range.");

		if ($a < 0 || $a > 255)
			throw new \RuntimeException("Alpha value is out of range.");

		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
		$this->a = $a;
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
