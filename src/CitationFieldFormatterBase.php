<?php

namespace Drupal\citation_select;

use Drupal\citation_select\CitationFieldFormatterInterface;

class CitationFieldFormatterBase implements CitationFieldFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function formatMultiple($node, $node_field, $csl_fields) {
    if (!$node->hasField($node_field) || $node->get($node_field)->isEmpty()) {
      return [];
    }

    $data = [];
    foreach ($csl_fields as $csl_field => $csl_type) {
      if ($csl_type == 'person') {
        $data[$csl_field] = $this->formatNames($this->getFieldValueList($node, $node_field));
      } else if ($csl_type == 'date') {
        $data[$csl_field] = $this->parseDate($this->getField($node, $node_field));
      } else {
        $data[$csl_field] = $this->getField($node, $node_field);
      }
    }
  }

  /**
   * Converts date string to CSL-JSON array format
   *
   * @param string $string
   * @return array Date formatted as CSL-JSON
   */
  protected function parseDate($string) {
    $date = date_parse($string);
    return [
      'date-parts' => [[
        $date['year'],
        $date['month'],
        $date['day'],
      ]],
    ];
  }

  /**
   * Gets field value from node
   *
   * @param $node Drupal node object
   * @param string $field Field name from node
   * @return Field value from node
   */
  protected function getField($node, $field) {
    return $node->get($field)->getValue()[0]['value'];
  }

  /**
   * Gets list of field values from node
   *
   * @param $node Drupal node object
   * @param string $field Field name from node
   * @return array List of field values from node
   */
  protected function getFieldValueList($node, $field) {
    $data = array_map(function($n) {
        return $n['value'];
      }, $node->get($field)->getValue());
    return $data;
  }

  /**
   * Gets list of field values from node
   *
   * @param array string[] List of strings to format into CSL-JSON format
   * @return array List of names formatted as CSL-JSON
   */
  protected function formatNames($list) {
    $data = [];
    foreach ($list as $name) {
      $data[] = $this->convertName($name);
    }
    return $data;
  }

  /**
   * Converts string to CSL-JSON list
   *
   * @param string String to convert into CSL-JSON list
   * @return array Name formatted as CSL-JSON
   */
  protected function convertName($name) {
    try {
      $name_parts = \Drupal::service('bibcite.human_name_parser')->parse($name);

      $name_map = [
        'prefix' => $name_parts['prefix'],
        'given' => $name_parts['first_name'],
        'family' => $name_parts['last_name'],
        'suffix' => $name_parts['suffix'],
      ];
    } catch (Exception $e) {
      $name_map = [
        'literal' => $name
      ];
    }
    return $name_map;
  }

}