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

use MIDAS\ApiProviderInterface;
use MIDAS\module\light\color;
use MIDAS\module\light\lock;
use MIDAS\module\light\driver\LightDriverInterface;
use MIDAS\module\light\driver\selector;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class controller implements ApiProviderInterface, BootableProviderInterface,
                            ControllerProviderInterface,
                            ServiceProviderInterface
{
	/** \brief Register all drivers.
	 *
	 * \details All drivers will be registered in the \p app Pimple container,
	 *  so they may be loaded on-demand by the \ref light class.
	 *
	 *
	 * \param app The attached Pimple container.
	 */
	private function register_drivers(Container $app)
	{
		foreach (glob(__DIR__.'/drivers/*.php') as $file) {
			require_once $file;

			if (isset($driver_name)) {
				$app['modules.light.drivers.'.$driver_name] = $app->factory(
					function (Container $app) use ($driver_name) {
						$classname = 'MIDAS\module\light\driver\\'.$driver_name;
						return new $classname($app);
					});

				unset($driver_name);
			}
		}
	}


	/** \brief Register the module.
	 *
	 * \details This class registers all required classes and the light drivers
	 *  in the \p app container.
	 *
	 *
	 * \param app The attached Pimple container.
	 */
	public function register(Container $app)
	{
		$app['modules.light.color'] = $app->factory(function (Container $app) {
			return new color;
		});

		$app['modules.light.driver'] = function (Container $app) {
			return new selector($app);
		};

		$app['modules.light.lock'] = $app->factory(function (Container $app) {
			return new lock;
		});


		$this->register_drivers($app);
	}


	/** \brief Boot the module.
	 *
	 * \details This function registers the modules configurations for Twig and
	 *  mounts the controller for UI and API usage.
	 *
	 *
	 * \param app The attached Silex application.
	 */
	public function boot(Application $app)
	{
		$app->register_twig_path('light', __DIR__.'/views');

		$app->mount('/light', $this);
		$app->mount_api('/light', $this);
	}


	/** \brief Register all API calls.
	 *
	 *
	 * \param app The attached Silex application.
	 */
	public function connect_api(Application $app)
	{
		$routes = $app['controllers_factory'];


		$routes->get("/{lamp}/color/{color}",
			function (LightDriverInterface $lamp, color $color) use ($app) {
				if ($lamp->set_color($color))
					return $app->json(array('status' => 'ok'));
				else
					return $app->json(array('status' => 'Resource busy'), 409);
		})
		->assert('lamp', '\d+')
		->assert('color', '[0-f]{6}')
		->convert('lamp', 'modules.light.driver:select')
		->convert('color', 'modules.light.color:convert')
		->bind('api.light.color');


		$routes->get("/{lamp}/brightness/{brightness}",
			function (LightDriverInterface $lamp, int $brightness) use ($app) {
				if ($lamp->set_brightness($brightness))
					return $app->json(array('status' => 'ok'));
				else
					return $app->json(array('status' => 'Resource busy'), 409);
		})
		->assert('id', '\d+')
		->assert('brightness', '[0-f]{2}')
		->convert('lamp', 'modules.light.driver:select')
		->convert('brightness', function ($brightness) {
			return hexdec($brightness);
		})
		->bind('api.light.brightness');


		return $routes;
	}


	/** \brief Register all UI interfaces.
	 *
	 *
	 * \param app The attached Silex application.
	 */
	public function connect(Application $app)
	{
		$routes = $app['controllers_factory'];


		$routes->get("/", function () use ($app) {
			return $app['twig']->render('@light/overview.twig',
				array('lamps' => $app['config']['light']));
		})
		->bind('light.home');


		$routes->get("/{id}", function ($id) use ($app) {
			if (!isset($app['config']['light'][$id]))
				$app->abort(404, "Light $id does not exist.");

			$lamp = $app['modules.light.driver']->select($id);

			return $app['twig']->render('@light/lamp.twig', array_merge(
				$app['config']['light'][$id],
				array(
					'brightness' => $lamp->get_brightness()
				)
			));
		})
		->assert('id', '\d+')
		->bind('light.lamp');
		return $routes;
	}
}

?>
