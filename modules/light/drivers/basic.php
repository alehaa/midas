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

namespace MIDAS\light\driver;

use MIDAS\light\driver\helper\rgb;


require_once __DIR__.'/rgb.php';


/* \brief Basic driver for controllers accepting only RGB values.
 *
 * \details This driver will be used for basic LED controllers, which accept RGB
 * hex values via UART or network only. The controllers have no special
 * capabilities, so all the rendering for transitions etc. have to be done by
 * the driver. */
class basic {
	/** \brief ID of the light source.
	 */
	protected $id;


	/** \brief Path of the device file.
	 */
	protected $file;


	/** \brief Time to render the transition length.
	 */
	protected $transition = 0;


	function __construct(array $config)
	{
		/* Check for required values. */
		foreach (array('file', 'id') as $required)
			if (!isset($config[$required]))
				throw new \RuntimeException("Required key '$required' ".
					"not defined in config.");


		/* Store configuration into variables. */
		$this->id = $config['id'];
		$this->file = $config['file'];

		if (isset($config['transition']))
			$this->transition = $config['transition'];


		/* Check, if the device file can be accessed. */
		if (!is_writable($this->file))
			throw new \RuntimeException("No write access to '".$this->file."'");
	}


	/** \brief Get the device-specific APCu key for \p keyword.
	 *
	 *
	 * \param keyword The APCu keyword to be used.
	 *
	 * \return The APCu key to be used.
	 */
	private function apcu_key($keyword)
	{
		return 'light.'.$this->id.'.'.$keyword;
	}


	/** \brief Get the APCu value for \p keyword.
	 *
	 * \details This function returns the value of the \p keyword value of the
	 *  APC cache (\p keyword will be processed by \ref apcu_key before), or the
	 *  default value \p default, if no value was found in the cache.
	 *
	 *
	 * \param keyword The APCu keyword to be used.
	 * \param default Default value to be returned, if no value could be found
	 *  in the cache.
	 *
	 * \return Either the value from the cache or the default value will be
	 *  returned.
	 */
	private function get_cache_or_default($keyword, $default)
	{
		$ret = apcu_fetch($this->apcu_key($keyword));
		if ($ret !== false)
			return $ret;
		else
			return $default;
	}


	/** \brief Store \p value in APCu cache with \p keyword.
	 *
	 * \details This function stores \p value to \p keyword in the APC cache (\p
	 *  keyword will be processed by \ref apcu_key before).
	 *
	 *
	 * \param keyword The APCu keyword to be used.
	 * \param value The value to be stored in the cache.
	 */
	private function set_cache($keyword, $value)
	{
		apcu_store($this->apcu_key($keyword), $value);
	}


	/** \brief Write the color value to the device file.
	 *
	 *
	 * \param r RGB red value to be sent.
	 * \param g RGB green value to be sent.
	 * \param b RGB blue value to be sent.
	 */
	protected function send_color(rgb $color)
	{
		file_put_contents($this->file, $color->toRGB()."\n");
	}


	/** \brief Make a transition from \p c1 to \p c2.
	 *
	 * \details This function renders a transition from color \p c1 to color \p
	 *  c2 in \ref transition_time seconds.
	 *
	 *
	 * \param c1 First color.
	 * \param c2 Second color.
	 */
	private function render_transition(rgb $c1, rgb $c2)
	{
		/* Calculate all variables needed for the transition. */
		$step = (1000000 * $this->transition) / 255;
		$delta_r = $c2->r - $c1->r;
		$delta_g = $c2->g - $c1->g;
		$delta_b = $c2->b - $c1->b;
		$delta_a = $c2->a - $c1->a;


		/* Loop for the transition. */
		for ($i = 0; $i < 256; $i++) {
			$trans_c = new rgb($c1->r + ($delta_r / 255) * $i,
			                   $c1->g + ($delta_g / 255) * $i,
			                   $c1->b + ($delta_b / 255) * $i,
			                   $c1->a + ($delta_a / 255) * $i);
			$this->send_color($trans_c);

			usleep($step);
		}
	}


	/** \brief Set the color.
	 *
	 * \details This function sets the color of the LED stripe to the new
	 *  defined color. If \ref transition is not null, a transition will be
	 *  rendered to fade in the new color smoothly.
	 *
	 *
	 * \param r RGB red value.
	 * \param g RGB green value.
	 * \param b RGB blue value.
	 */
	public function set_color($r, $g, $b)
	{
		/* Get the current brightness and create a new color object. */
		$brightness = $this->get_cache_or_default('a', 255);
		$color = new rgb($r, $g, $b, $brightness);


		/* If transitions are disabled for this lamp, the new color can be send
		 * directly. */
		if ($this->transition == 0)
			$this->send_color($color);

		/* If transitions are enabled for this lamp, get the old color and
		 * render a transition from the old to the new color. */
		else {
			$cur = new rgb($this->get_cache_or_default('r', 0),
			               $this->get_cache_or_default('g', 0),
			               $this->get_cache_or_default('b', 0),
			               $brightness);

			$this->render_transition($cur, $color);
		}

		/* Store the new color in the cache. */
		$this->set_cache('r', $r);
		$this->set_cache('g', $g);
		$this->set_cache('b', $b);
	}


	/** \brief Set the brightness.
	 *
	 * \details This function sets the brightness of the LED stripe to the new
	 *  defined value. If \ref transition is not null, a transition will be
	 *  rendered to fade in the new color smoothly.
	 *
	 *
	 * \param brightness New brightness value.
	 */
	public function set_brightness($brightness)
	{
		/* Get the current color and create a new color object with the new
		 * brightness. */
		$r = $this->get_cache_or_default('r', 0);
		$g = $this->get_cache_or_default('g', 0);
		$b = $this->get_cache_or_default('b', 0);
		$color = new rgb($r, $g, $b, $brightness);


		/* If transitions are disabled for this lamp, the new color can be send
		 * directly. */
		if ($this->transition == 0)
			$this->send_color($color);

		/* If transitions are enabled for this lamp, get the old color and
		 * render a transition from the old to the new color. */
		else {
			$cur = new rgb($r, $g, $b, $this->get_cache_or_default('a', 255));
			$this->render_transition($cur, $color);
		}

		/* Store the new brightness in the cache. */
		$this->set_cache('a', $brightness);
	}


	/** \brief Get the current brightness.
	 *
	 * \details This function gets the current brightness of the LED stripe from
	 *  the cache.
	 *
	 *
	 * \return If a value could be found in the cache, it will be returned.
	 *  Otherwise 255 will be returned.
	 */
	public function get_brightness()
	{
		return $this->get_cache_or_default('a', 255);
	}
};

?>
