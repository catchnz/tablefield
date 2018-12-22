<?php

namespace Drupal\tablefield\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

// Use Drupal\tablefield\Utility\Tablefield;.
/**
 * Plugin implementation of the default Tablefield formatter.
 *
 * @FieldFormatter (
 *   id = "tablefield",
 *   label = @Translation("Tabular View"),
 *   field_types = {
 *     "tablefield"
 *   }
 * )
 */
class TablefieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'row_header' => 1,
      'column_header' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['row_header'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display first row as a table header'),
      '#default_value' => $this->getSetting('row_header'),
    ];

    $elements['column_header'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display first column as a table header'),
      '#default_value' => $this->getSetting('column_header'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $row_header = $this->getSetting('row_header');
    $column_header = $this->getSetting('column_header');

    if ($row_header) {
      $summary[] = $this->t('First row as a table header');
    }

    if ($column_header) {
      $summary[] = $this->t('First column as a table header');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {

    $field = $items->getFieldDefinition();
    $field_name = $field->getName();
    $field_settings = $field->getSettings();

    $entity = $items->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    $row_header = $this->getSetting('row_header');
    $column_header = $this->getSetting('column_header');

    $elements = [];
    $header = [];

    foreach ($items as $delta => $table) {

      if (!empty($table->value)) {
        // Tablefield::rationalizeTable($table->value);.
        $tabledata = $table->value;
        $caption = $tabledata['caption'];
        unset($tabledata['caption']);

        // Run the table through input filters.
        foreach ($tabledata as $row_key => $row) {
          foreach ($row as $col_key => $cell) {
            $tabledata[$row_key][$col_key] = [
              'data' => empty($table->format) ? $cell : check_markup($cell, $table->format),
              'class' => ['row_' . $row_key, 'col_' . $col_key],
            ];
          }
        }

        if ($row_header) {

          // Pull the header for theming.
          $header_data = array_shift($tabledata);

          // Check for an empty header, if so we don't want to theme it.
          $has_header = FALSE;
          foreach ($header_data as $cell) {
            if (strlen($cell['data']) > 0) {
              $has_header = TRUE;
              break;
            }
          }
          if ($has_header) {
            $header = $header_data;
          }
        }

        if ($column_header) {
          foreach ($tabledata as $row_key => $row) {
            if (strlen($tabledata[$row_key][0]['data']) > 0) {
              $tabledata[$row_key][0]['header'] = TRUE;
            }
          }
        }

        $render_array = [];

        // If the user has access to the csv export option, display it now.
        if ($field_settings['export'] && $entity_id && \Drupal::currentUser()->hasPermission('export tablefield')) {

          $route_params = [
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'field_name' => $field_name,
            'langcode' => $items->getLangcode(),
            'delta' => $delta,
          ];

          $url = Url::fromRoute('tablefield.export', $route_params);

          $render_array['export'] = [
            '#type' => 'container',
            '#attributes' => [
              'id' => 'tablefield-export-link-' . $delta,
              'class' => 'tablefield-export-link',
            ],
          ];
          $render_array['export']['link'] = [
            '#type' => 'link',
            '#title' => $this->t('Export Table Data'),
            '#url' => $url,
          ];
        }

        $render_array['tablefield'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $tabledata,
          '#attributes' => [
            'id' => 'tablefield-' . $entity_type . '-' . $entity_id . '-' . $field_name . '-' . $delta,
            'class' => [
              'tablefield',
            ],
          ],
          '#caption' => $caption,
          '#prefix' => '<div id="tablefield-wrapper-' . $entity_type . '-' . $entity_id . '-' . $field_name . '-' . $delta . '" class="tablefield-wrapper">',
          '#suffix' => '</div>',
        ];

        $elements[$delta] = $render_array;
      }

    }
    return $elements;
  }

}
