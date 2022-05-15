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

namespace Roducks\Page;

use Roducks\Services\Db;
use Roducks\Services\Form;
use Roducks\Lib\Request\Http;
use Roducks\Di\ContainerInterface;

abstract class JsonToken extends Json {
  const TOKEN = 'RDKS_FORM_TOKEN';

  /**
   * @var \Roducks\Services\Form $form
   */
  protected $form;

  /**
   * 
   */
  public function __construct(array $settings, Db $db, Form $form)
  {
    parent::__construct($settings, $db, $form);
    $token = $settings['request']->get('token', NULL);
    $this->form = $form;

    if (empty($token) || $token !== $this->form->getHash(static::TOKEN)) {
      $this->data('error', TRUE);
      $this->data('message', "A valid token is required.");
      $this->output(401);
    }
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('form')
    );
  }

  protected function output($code = 200)
  {
    $this->_output($code);
    $this->form->unsetHash(static::TOKEN);
    exit;
  }

}
