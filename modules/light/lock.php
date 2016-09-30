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


/** \brief Helper class to lock light access to avoid race conditions.
 */
class lock
{
	private $state = array(); ///< Array that stores the state of all locks.


	/** \brief Destructor.
	 *
	 * \details The destructor unlocks any locks it owns.
	 */
	function __destruct()
	{
		foreach (array_keys($this->state) as $key)
			$this->unlock($key);
	}


	/** \brief Try to lock the \p key.
	 *
	 * \details This function locks the \p key. If \p ttl is non-null, the ttl
	 *  of the key will be set to the desired time to avoid not unlocked locks
	 *  after crashes.
	 *
	 *
	 * \return If the lock could be locked, true will be returned. Otherwise
	 *  false.
	 */
	function trylock(string $key, int $ttl = null)
	{
		if (apcu_add($key, true, ($ttl == null) ? 0 : $ttl)) {
			$this->state[$key] = true;
			return true;
		}

		return false;
	}


	/** \brief Unlock the lock with \p key, if it's locked.
	 *
	 * \details If the lock was locked by us, this function will unlock it. If
	 *  the lock was locked by another process, this function will simply do
	 *  nothing.
	 */
	function unlock(string $key)
	{
		if (isset($this->state[$key])) {
			apcu_delete($key);
			unset($this->state[$key]);
		}
	}
}

?>
