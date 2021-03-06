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

require_once __DIR__.'/color.php';
require_once __DIR__.'/configurable.php';
require_once __DIR__.'/controller.php';
require_once __DIR__.'/lock.php';
require_once __DIR__.'/select_driver.php';
require_once __DIR__.'/drivers/interface.php';


use MIDAS\module\light\controller;


$app->register(new controller());

?>
