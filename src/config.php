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
use MIDAS\ro_array;
use Pimple\Container;
use Pimple\ServiceProviderInterface;


class config extends ro_array implements ArrayAccess, ServiceProviderInterface
{
	/** \brief Config array.
	 *
	 * \details This variable stores the configurations for all modules in a
	 *  single array.
	 */
	private $data;


	/** \brief Constructor.
	 *
	 * \details Creates a new Config object and loads all configuration files in
	 *  \p paths and their subdirectories.
	 *
	 *
	 * \param paths Array of paths containing all required configuration files.
	 */
	public function __construct(array $paths)
	{
		$this->load($paths);
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
	private function load(array $paths)
	{
		foreach ($paths as $path) {
			/* Recurse into subdirectories. */
			$sub = glob("$path/[^.]*", GLOB_ONLYDIR);
			if (!empty($sub))
				$this->load($sub);


			/* Load the configuration files. */
			foreach (glob("$path/*.json") as $file) {
				if (($conf = file_get_contents($file)) === false)
					throw new \RuntimeException("File '$file' not readable.");

				if (($conf = json_decode($conf, true)) === null)
					throw new \RuntimeException("File '$file' is not a valid ".
						"JSON file.");

				foreach (array('id', 'module') as $required)
					if (!isset($conf[$required]))
						throw new \RuntimeException("Required key '$required' ".
							"not defined in config file '$file'.");


				$this->data[$conf['module']][$conf['id']] = $conf;
				unset($this->data[$conf['module']][$conf['id']]['module']);
			}
		}
	}


	/*
	 * ArrayAccess methods.
	 */


	/** \brief Whether or not an \p offset exists.
	 *
	 * \details This method is executed when using isset() or empty() on the
	 *  config object.
	 *
	 *
	 * \param offset An offset to check for.
	 *
	 * \return Returns TRUE on success or FALSE on failure.
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}


	/** \brief Returns the value at specified \p offset.
	 *
	 * \details This method is executed when checking if \p offset is empty() or
	 *  when accessing the object.
	 *
	 *
	 * \param offset The offset to retrieve.
	 *
	 * \return Returns the configuration object on success or FALSE on failure.
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
			return $this->data[$offset];

		return false;
	}


	/*
	 * ServiceProviderInterface methods.
	 */


	/** \brief Register the provider.
	 *
	 * \details This function is called by Silex to register the provider. It
	 *  will add the 'config' key to \p app to give public read access to the
	 *  configuration.
	 *
	 *
	 * \param app The attached Pimple container.
	 */
	public function register(Container $app)
	{
		$app['config'] = $this;
	}
}

?>
