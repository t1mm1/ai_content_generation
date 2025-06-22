<?php

namespace Drupal\ai_content_generation\Form;

use Drupal\ai\AiProviderPluginManager;
use Drupal\ai\Enum\AiModelCapability;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for settings.
 */
class SettingsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * Config settings.
   */
  const CONFIG_NAME = 'ai_content_generation.settings';

  /**
   * AI Provider service.
   *
   * @var \Drupal\ai\AiProviderPluginManager
   */
  protected $providerManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typed_config_manager,
    AiProviderPluginManager $provider_manager,
  ) {
    parent::__construct(
      $config_factory,
      $typed_config_manager,
    );
    $this->providerManager = $provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ConfigFormBase|SettingsForm|static {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('ai.provider'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ai_content_generation_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      static::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(static::CONFIG_NAME);

    $models = $this->providerManager->getSimpleProviderModelOptions('chat', TRUE, TRUE, [
      AiModelCapability::ChatWithImageVision,
    ]);

    $form['role'] = [
      '#title' => $this->t('Role'),
      '#type' => 'textarea',
      '#default_value' => $config->get('role') ?? '',
      '#description' => $this->t('Role used for generating the text.'),
      '#required' => TRUE,
    ];

    $form['prompt'] = [
      '#title' => $this->t('Prompt'),
      '#type' => 'textarea',
      '#default_value' => $config->get('prompt') ?? '',
      '#description' => $this->t('Prompt used for generating the text.'),
      '#required' => TRUE,
    ];

    $form['model'] = [
      '#title' => $this->t('AI provider/model'),
      '#type' => 'select',
      '#options' => $models,
      '#default_value' => $config->get('model') ?? '',
      '#empty_option' => $this->t('Use Default AI Model'),
      '#description' => $this->t('AI model to use for generating the text.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('role', $form_state->getValue('role'))
      ->set('prompt', $form_state->getValue('prompt'))
      ->set('model', $form_state->getValue('model'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
