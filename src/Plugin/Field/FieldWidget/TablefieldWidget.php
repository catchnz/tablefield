<?php

/**
 * @file
 * Contains \Drupal\tablefield\Plugin\Field\FieldWidget\TablefieldWidget.
 */

namespace Drupal\tablefield\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;


/**
 * Plugin implementation of the 'tablefield' widget.
 *
 * @FieldWidget (
 *   id = "tablefield",
 *   label = @Translation("Table Field"),
 *   field_types = {
 *     "tablefield"
 *   },
 * )
 */
class TablefieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $is_field_settings_default_widget_form = $form_state->getBuildInfo()['form_id'] == 'field_ui_field_edit_form' ? 1 : 0;

    $field = $items[0]->getFieldDefinition();
    //$field_name = $field->getName();
    $field_settings = $field->getSettings();

    if (!empty($field->default_value[$delta])) {
      $field_default = $field->default_value[$delta];
    }

    if (isset($items[$delta]->value)) {
      $default_value = $items[$delta];
    }
    elseif (!$is_field_settings_default_widget_form && !empty($field_default)) {
      // load field settings defaults in case current item is empty
      $default_value = $field_default;
    }
    else {
      $default_value = (object) array('value' => array(), 'rebuild' => array());
    }

    $cols = isset($default_value->rebuild['cols']) ? $default_value->rebuild['cols'] : 5;
    $rows = isset($default_value->rebuild['rows']) ? $default_value->rebuild['rows'] : 5;

    $element = array(
      '#type' => 'tablefield',
      '#description' => $this->t('The first row will appear as the table header. Leave the first row blank if you do not need a header.'),
      '#cols' => $cols,
      '#rows' => $rows,
      '#default_value' => $default_value->value,
      '#lock' => !$is_field_settings_default_widget_form && $field_settings['lock_values'],
      '#locked_cells' => !empty($field_default->value) ? $field_default->value : array(),
      '#rebuild' => \Drupal::currentUser()->hasPermission('rebuild tablefield'),
      '#import' => \Drupal::currentUser()->hasPermission('import tablefield'),
    ) + $element;

    if ($is_field_settings_default_widget_form) {
      $element['#description'] = $this->t('This form defines the table field defaults, but the number of rows/columns and content can be overridden on a per-node basis. The first row will appear as the table header. Leave the first row blank if you do not need a header.');

      // This we need in the TablefieldItem::isEmpty check
      $element['is_field_settings'] = array(
        '#type' => 'value',
        '#value' => 1,
      );
    }

    $element['#element_validate'][] = array($this, 'validateTablefield');

    //$form['#attributes']['enctype'] = 'multipart/form-data';
  
    // Allow the user to select input filters
    if (!empty($field_settings['cell_processing'])) {
      $element['#base_type'] = $element['#type'];
      $element['#type'] = 'text_format';
      if (!empty($field_default['format']) || !empty($items[$delta]->format)) {
        $element['#format'] = isset($items[$delta]->format) ? $items[$delta]->format : $field_default['format'];
      }
      $element['#editor'] = FALSE;
    }

    return $element;
  }

  public function validateTablefield(array &$element, FormStateInterface &$form_state, array $form) {
    if ($element['#required'] && $form_state->getTriggeringElement()['#type'] == 'submit') {
      $items = new FieldItemList($this->fieldDefinition);
      $this->extractFormValues($items, $form, $form_state);
      if (!$items->count()) {
        $form_state->setError($element, t('!name field is required.', array('!name' => $this->fieldDefinition->getLabel())));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element[0];
  }

}