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

use SilMod;


class MIDAS extends SilMod\SilMod
{
	/** \brief Config array.
	 *
	 * \details This variable stores the configurations for all modules in a
	 *  single array. The data may be accessed and modified by all modules and
	 *  is not protected.
	 */
	public $config = array();


	function __construct(array $options = array())
	{
		if (isset($options['config.path'])) {
			/* If the config path is a single string, convert it to an array
			 * with only one element. This allows us to optimize the code for
			 * array usage instead of handling both cases (array AND string). */
			if (is_string($options['config.path']))
				$options['config.path'] = array($options['config.path']);

			$this->load_config($options['config.path']);
		}


		parent::__construct($options);
	}


	/** \brief Load all configuration files in \p paths.
	 *
	 * \details Load all files with .json file extension in paths defined by
	 *  \p paths. The configurations will be saved in the \ref config array.
	 *
	 *
	 * \param paths Array of strings pointing to paths containing all required
	 *  configuration files.
	 */
	private function load_config(array $paths)
	{
		foreach ($paths as $path) {
			if (!is_dir($path))
				throw new \RuntimeException("Path '$path' does not exist.");

			foreach (glob($path.'/*.json') as $file) {
				if (($conf = file_get_contents($file)) === false)
					throw new \RuntimeException("File '$file' not readable.");

				if (($conf = json_decode($conf, true)) === null)
					throw new \RuntimeException("File '$file' is not a valid ".
						"JSON file.");

				foreach (array('id', 'module') as $required)
					if (!isset($conf[$required]))
						throw new \RuntimeException("Required key '$required' ".
							"not defined in config file '$file'.");


				$this->config[$conf['module']][$conf['id']] = $conf;
				unset($this->config[$conf['module']][$conf['id']]['module']);
			}
		}
	}


	/** \brief Add routes to API routing.
	 *
	 * \details This function is a wrapper for the register_routes function of
	 *  SilMod. All defined routes will not be mounted in the \p name path, but
	 *  the \p name path in the api subdirectory.
	 *
	 *
	 * \param name Module name.
	 * \param callback Function to be called to register the modules routes.
	 */
	public function register_api_routes(string $name, callable $callback)
	{
		parent::register_routes('api/'.$name, $callback);
	}
};


?>
