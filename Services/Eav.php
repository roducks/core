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

namespace Roducks\Services;

use Roducks\Page\Service;

class Eav extends Service {
  protected $_model = NULL;
  protected $_entity = NULL;
  protected $_id = 0;

  public function __construct(array $settings, $db)
  {
    parent::__construct($settings, $db);
    $this->_model = $this->db->model('eav');
  }

  public function setEntity($name, $id)
  {
    $this->_entity = $name;
    $this->_id = $id;

    return $this;
  }

  public function setAttribute($name, $value)
  {
    $this->_model->prepare();
    $this->_model->set('id_rel:int', $this->_id);
    $this->_model->set('entity', $this->_entity);
    $this->_model->set('attribute', $name);
    $this->_model->set('value', $value);
    $this->_model->set('created_at', date('Y-m-d H:i:s'));
    $this->_model->save();

    return $this->_model->getId();
  }

  public function getAttribute($name)
  {
    $this->_model->select([
      'id',
      'value',
    ])->filter([
      ['id_rel:int', '=', $this->_id],
      ['entity:str', '=', $this->_entity],
      ['attribute:str', '=', $name],
    ])->execute();

    $data = [];

    while($row = $this->_model->fetch()) {
      $data[] = $row;
    }

    return $data;
  }

  public function getAttributeValue($name)
  {
    $attr = $this->getAttribute($name);

    return $attr[0]['value'] ?? NULL;
  }
}
