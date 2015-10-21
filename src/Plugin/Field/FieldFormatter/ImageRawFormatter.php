<?php

/**
 * @file
 * Contains \Drupal\image_raw_formatter\Plugin\Field\FieldFormatter\ImageRawFormatter.
 */

namespace Drupal\image_raw_formatter\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use \InvalidArgumentException;

/**
 * Plugin implementation of the 'image_raw_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "image_raw_formatter",
 *   label = @Translation("Image Raw"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageRawFormatter extends ImageFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'image_style' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);
    $element['image_style'] = array(
      '#title' => t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => $image_styles,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    $image_style_setting = $this->getSetting('image_style');
    if (isset($image_styles[$image_style_setting])) {
      $summary[] = t('Image style: @style', array('@style' => $image_styles[$image_style_setting]));
    }
    else {
      $summary[] = t('Original image');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    $image_style_setting = $this->getSetting('image_style');

    // Determine if Image style is required.
    $image_style = NULL;
    if (!empty($image_style_setting)) {
      $image_style = entity_load('image_style', $image_style_setting);
    }

    foreach ($items as $delta => $item) {
      if ($item->entity) {
        $image_uri = $item->entity->getFileUri();

        // Get image style URL
        if ($image_style) {
          $image_uri = ImageStyle::load($image_style->getName())->buildUrl($image_uri);
        } else {
          // Get absolute path for original image
          $image_uri = $item->entity->url();
        }

        $elements[$delta] = array(
          '#markup' => $image_uri,
        );
      }
    }

    return $elements;
  }
}
