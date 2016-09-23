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

use MIDAS\light\driver;


/** \brief Helfer function to get the required driver.
 *
 *
 * \param app Handle to the core app.
 * \param id ID of the lamp.
 *
 * \return Initialized driver object.
 */
function get_driver(\MIDAS\MIDAS $app, int $id)
{
	/* Check if all necessary variables are set in the configuration. */
	if (!isset($app->config['light'][$id]))
		throw new \RuntimeException("Unknown light source '$id'.");

	if (!isset($app->config['light'][$id]['driver']))
		throw new \RuntimeException("No driver set for light source '$id'.");


	/* Load the driver. */
	$driver = $app->config['light'][$id]['driver'];
	if (!is_file(__DIR__.'/drivers/'.$driver.'.php'))
		throw new \RuntimeException("Unknown driver '$driver' for light source".
			" '$id'.");

	require_once __DIR__.'/drivers/'.$driver.'.php';


	/* Initialize the driver. */
	$classname = 'MIDAS\light\driver\\'.$driver;
	return new $classname($app->config['light'][$id]);
}


/*
 * Register the module.
 */
$app->register_module("light");


/*
 * Register the API calls.
 */
$app->register_api_routes("light", function($app, $routes) {
	$routes->get("/{id}/color/{r}{g}{b}", function ($id, $r, $g, $b) use ($app)
		{
		$driver = get_driver($app, $id);
		$driver->set_color(hexdec($r), hexdec($g), hexdec($b));

		return json_encode(array('status' => 'ok'));
	})
	->assert('id', '\d+')
	->assert('r', '[0-f]{2}')
	->assert('g', '[0-f]{2}')
	->assert('b', '[0-f]{2}');


	$routes->get("/{id}/brightness/{brightness}", function ($id, $brightness)
		use ($app) {
		$driver = get_driver($app, $id);
		$driver->set_brightness($brightness);

		return json_encode(array('status' => 'ok'));
	})
	->assert('id', '\d+')
	->assert('brightness', '[0-f]{2}');
});

?>
