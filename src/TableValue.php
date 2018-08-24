<?php

namespace Drupal\tablefield;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedData;
use Drupal\tablefield\Plugin\Field\FieldType\TablefieldItem;

/**
 * A computed property for Search API indexing.
 */
class TableValue extends TypedData {

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TablefieldItem $parent = NULL) {
    parent::__construct($definition, $name, $parent);
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    /** @var \Drupal\tablefield\Plugin\Field\FieldType\TablefieldItem $item */
    $item = $this->getParent();
    $value = '';
    if (isset($item->value)) {
      foreach ($item->value as $row) {
        $value .= implode(' ', $row) . ' ';
      }
      $value = trim($value);
    }
    return $value;
  }

}
