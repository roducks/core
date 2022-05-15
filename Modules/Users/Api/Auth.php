<?php
/**
 *
 * This file is part of Roducks.
 *
 *    Roducks is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Roducks is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with Roducks.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Roducks\Modules\Users\Api;

use Roducks\Lib\Request\Request;

class Auth extends Users {

  public function federate(Request $request)
  {
    $user = $this->user->auth($request->get('email'), $request->get('password'));

    if (!empty($user)) {
      $token = $this->setToken($user);

      $this->data('type', "Bearer");
      $this->data('expires', static::JWT_EXPIRATION);
      $this->data('access_token', $token);
    }
    else {
      return $this->setError('Authentication failed', 401);
    }

    return $this->output();
  }

}
