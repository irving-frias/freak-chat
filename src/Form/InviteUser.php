<?php declare(strict_types = 1);

namespace Drupal\freak_chat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Freak Chat form.
 */
final class InviteUser extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new YourCustomClass object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'freak_chat_invite_user';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $allUsers = $this->getAllUsers();

    $form['users'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Users'),
      '#options' => $allUsers['display_name'],
      '#required' => TRUE,
      '#multiple' => TRUE,
      '#default_value' => [],
      '#description' => $this->t('Select users to invite.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

  private function getAllUsers() {
    $query = $this->entityTypeManager->getStorage('user')->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('status', 1); // Filter by active users (optional).

    // Execute the query and load user entities.
    $user_ids = $query->execute();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($user_ids);
    $users_data = [];

    foreach ($users as $user) {
      $users_data['id'][] = $user->id();
      $users_data['display_name'][] = $user->getDisplayName();
    }

    return $users_data;
  }
}
