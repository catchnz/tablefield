<?php

namespace Drupal\tablefield;

/**
 * Contains helper functions for process table structure.
 */
trait TableFieldProcessTableTrait {

  /**
   * Remove empty rows.
   *
   * @param array $table
   *   The current table.
   *
   * @return array
   *   Processed table.
   */
  protected function removeEmptyRows(array $table) : array {
    foreach ($table as $row_key => $row) {
      if ($this->checkIfGivenRowIsEmpty($row)) {
        unset($table[$row_key]);
      }
    }

    return $table;
  }

  /**
   * Remove empty columns.
   *
   * @param array $table
   *   The current table.
   *
   * @return array
   *   Processed table.
   */
  protected function removeEmptyColumns(array $table) : array {
    $table = $this->transposeTable($table);
    $table = $this->removeEmptyRows($table);
    return $this->transposeTable($table);
  }

  /**
   * Transpose give table.
   *
   * @param array $table
   *   The current table.
   *
   * @return array
   *   Transposed table.
   */
  protected function transposeTable(array $table) : array {
    if (empty($table)) {
      return $table;
    }

    $transposed_table = [];
    foreach ($table as $row_key => $column) {
      foreach ($column as $cell_key => $cell_value) {
        $transposed_table[$cell_key][$row_key] = $cell_value;
      }
    }

    return $transposed_table;
  }

  /**
   * Check if given table`s row is empty.
   *
   * @param array $row
   *   The given table's row.
   *
   * @return bool
   *   TRUE - if given tables`s row is empty.
   */
  protected function checkIfGivenRowIsEmpty(array $row) : bool {
    // Removed the row`s 'weight' property because of it is metadata element
    // should not to be considered as row`s data element.
    unset($row['weight']);
    return !(count($row) === 0 || array_filter($row));
  }

}
