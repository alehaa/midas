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

namespace MIDAS\module;

use MIDAS\ApiProviderInterface;
use MIDAS\lock;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class light implements ApiProviderInterface, ControllerProviderInterface,
                       ServiceProviderInterface
{
	/** \brief Helper function to get the required driver.
	 *
	 *
	 * \param app Handle to the core app.
	 * \param id ID of the lamp.
	 *
	 * \return Initialized driver object.
	 */
	public function get_driver(Application $app, $id)
	{
		/* Check if all necessary variables are set in the configuration. */
		if (!isset($app['config']['light'][$id]))
			throw new \RuntimeException("Light $id does not exist.");

		if (!isset($app['config']['light'][$id]['driver']))
			throw new \RuntimeException("Light $id has no driver defined.");


		/* Load the driver. */
		$driver = $app['config']['light'][$id]['driver'];
		if (!is_file(__DIR__.'/drivers/'.$driver.'.php'))
			throw new \RuntimeException("Unknown driver $driver for light $id");

		require_once __DIR__.'/drivers/'.$driver.'.php';


		/* Initialize the driver. */
		$classname = 'MIDAS\light\driver\\'.$driver;
		return new $classname($app['config']['light'][$id]);
	}


	/*
	 * ServiceProviderInterface methods.
	 */


	/** \brief Register the module.
	 *
	 *
	 * \param app The attached Pimple container.
	 */
	public function register(Container $app)
	{
		$app['modules.light'] = $this;

		$app->register_twig_path('light', __DIR__.'/views');
	}


	/*
	 * ControllerProviderInterface methods.
	 */


	/** \brief Register the UI.
	 */
	public function connect(Application $app)
	{
		$routes = $app['controllers_factory'];


		/* Overview. */
		$routes->get("/", function () use ($app) {
			return $app['twig']->render('@light/overview.twig',
				array('lamps' => $app['config']['light']));
		})
		->bind('light.home');

		/* Lamp detail page. */
		$routes->get("/{id}", function ($id) use ($app) {
			if (!isset($app['config']['light'][$id]))
				$app->abort(404, "Light $id does not exist.");

			$driver = $app['modules.light']->get_driver($app, $id);

			return $app['twig']->render('@light/lamp.twig', array_merge(
				$app['config']['light'][$id],
				array(
					'brightness' => $driver->get_brightness()
				)
			));
		})
		->assert('id', '\d+')
		->bind('light.lamp');


		return $routes;
	}


	/*
	 * APIProviderInterface methods.
	 */


	/** \brief Register the API.
	 */
	public function connect_api(Application $app)
	{
		$routes = $app['controllers_factory'];


		/* Change the color. */
		$routes->get("/{id}/color/{r}{g}{b}", function ($id, $r, $g, $b)
			use ($app) {
			$lock = new lock('light.'.$id);
			if (!$lock->trylock())
				return $app->json(array('status' => 'Resource busy'), 409);

			$driver = $app['modules.light']->get_driver($app, $id);
			$driver->set_color(hexdec($r), hexdec($g), hexdec($b));

			$lock->unlock();

			return $app->json(array('status' => 'ok'));
		})
		->assert('id', '\d+')
		->assert('r', '[0-f]{2}')
		->assert('g', '[0-f]{2}')
		->assert('b', '[0-f]{2}')
		->bind('api.light.color');

		/* Change the brightness. */
		$routes->get("/{id}/brightness/{brightness}", function ($id, $brightness)
			use ($app) {
			$lock = new lock('light.'.$id);
			if (!$lock->trylock())
				return $app->json(array('status' => 'Resource busy'), 409);

			$driver = $app['modules.light']->get_driver($app, $id);
			$driver->set_brightness(hexdec($brightness));

			$lock->unlock();

			return $app->json(array('status' => 'ok'));
		})
		->assert('id', '\d+')
		->assert('brightness', '[0-f]{1,2}')
		->bind('api.light.brightness');


		return $routes;
	}
}

?>
