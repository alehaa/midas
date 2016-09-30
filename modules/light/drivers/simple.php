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
use MIDAS\module\light\driver\basic;


$driver_name = 'simple'; ///< The name of this driver.


/* \brief Simple driver for controllers accepting only RGB values.
 *
 * \details This driver will be used for basic LED controllers, which accept RGB
 * hex values via UART or network only. The controllers have no special
 * capabilities, so all the rendering for transitions etc. have to be done by
 * the driver.
 */
class simple extends basic {
	/** \brief Get the device-specific APCu key for \p keyword.
	 *
	 *
	 * \param keyword The APCu keyword to be used.
	 *
	 * \return The APCu key to be used.
	 */
	private function cache_key(string $keyword)
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
	 * \return Either the value from the cache or \p default will be returned.
	 */
	private function get_cache_or_default(string $keyword, $default)
	{
		$ret = apcu_fetch($this->cache_key($keyword));
		return ($ret !== false) ? $ret : $default;
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
	private function set_cache(string $keyword, $value)
	{
		apcu_store($this->cache_key($keyword), $value);
	}


	/** \brief Get the current color.
	 *
	 * \return The current color object.
	 */
	private function get_current_color()
	{
		$color = $this->app['modules.light.color'];
		$color->convert($this->get_cache_or_default('color', '000000'));
		$color->a = $this->get_cache_or_default('brightness', 255);

		return $color;
	}


	/** \brief Write the color value to the device file.
	 *
	 *
	 * \param color Color to be send.
	 */
	private function send(color $color)
	{
		file_put_contents($this->file, $color->toRGB()."\n");
	}


	/** \brief Make a transition from the current color to \p color.
	 *
	 * \details This function renders a transition from the current color
	 *  received by \ref get_current_color to the new color \p color in \ref
	 *  transition seconds.
	 *
	 *
	 * \param color The color to fade to.
	 */
	private function transition(color $color)
	{
		/* If transitions are disabled for this lamp, the new color can be
		 * send directly. */
		if ($this->transition == 0) {
			$this->send($color);
			return;
		}


		/* If transitions are enabled for this lamp, get the old color and
		 * render a transition from the old to the new color. */
		$current = $this->get_current_color();


		/* Calculate all variables needed for the transition. */
		$step = (1000000 * $this->transition) / 255;
		$delta_r = $color->r - $current->r;
		$delta_g = $color->g - $current->g;
		$delta_b = $color->b - $current->b;
		$delta_a = $color->a - $current->a;


		/* Loop for the transition. */
		$trans = $this->app['modules.light.color'];
		for ($i = 0; $i < 256; $i++) {
			$trans->r = $current->r + ($delta_r / 255) * $i;
			$trans->g = $current->g + ($delta_g / 255) * $i;
			$trans->b = $current->b + ($delta_b / 255) * $i;
			$trans->a  =$current->a + ($delta_a / 255) * $i;

			$this->send($trans);

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
	 * \param color The color to be set.
	 */
	public function set_color(color $color)
	{
		$this->is_configured();


		/* Try to lock the device. If the device is locked we can't send colors
		 * now and have to abort the process. */
		$lock = $this->app['modules.light.lock'];
		if (!$lock->trylock($this->cache_key('lock'), 30))
			return false;

		/* Get the current color and adjust the brightness of the color
		 * object. */
		$brightness = $this->get_cache_or_default('brightness', 255);
		$color->a = $brightness;

		/* Fade to the new color and safe it in the cache. */
		$this->transition($color);
		$this->set_cache('color', $color->toRGB());

		/* Unlock the device and return success. */
		$lock->unlock($this->cache_key('lock'));

		return true;
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
	public function set_brightness(int $brightness)
	{
		$this->is_configured();


		/* Try to lock the device. If the device is locked we can't send colors
		 * now and have to abort the process. */
		$lock = $this->app['modules.light.lock'];
		if (!$lock->trylock($this->cache_key('lock'), 30))
			return false;

		/* Get the current color and adjust the brightness of the color
		 * object. */
		$color = $this->get_current_color();
		$color->a = $brightness;

		/* Fade to the new color and safe it in the cache. */
		$this->transition($color);
		$this->set_cache('brightness', $brightness);

		/* Unlock the device and return success. */
		$lock->unlock($this->cache_key('lock'));

		return true;
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
		return $this->get_cache_or_default('brightness', 255);
	}
}


?>
