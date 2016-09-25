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


class Lock
{
	private $key; ///< The lock key.
	private $ttl; ///< The ttl for the key.
	private $locked = false; ///< The state of the lock.


	/** \brief Create a new lock for \p key.
	 *
	 *
	 * \param key The key to be used.
	 * \param ttl The TTL to be used.
	 */
	function __construct($key, $ttl = null)
	{
		$this->key = $key;
		$this->ttl = ($ttl == null) ? 0 : $ttl;
	}


	/** \brief Destroy and unlock the lock.
	 */
	function __destruct()
	{
		$this->unlock();
	}


	/** \brief Try to lock the \ref key.
	 *
	 * \details This function locks the \ref key. If \ref ttl is non-null, the
	 *  ttl of the key will be set to the desired time to avoid not unlocked
	 *  locks after crashes.
	 *
	 *
	 * \return If the lock could be locked, true will be returned. Otherwise
	 *  false will be returned.
	 */
	function trylock()
	{
		$this->locked = apcu_add($this->key, true, $this->ttl);
		return $this->locked;
	}


	/** \brief Unlock the lock, if it's locked.
	 *
	 * \details If the lock was locked by us, this function will unlock it. If
	 *  the lock was locked by another class, this function will simply do
	 *  nothing.
	 */
	function unlock()
	{
		if ($this->locked)
			apcu_delete($this->key);
	}
}

?>
