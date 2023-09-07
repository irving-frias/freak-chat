<?php declare(strict_types = 1);

namespace Drupal\freak_chat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Provides a Freak Chat form.
 */
final class RegisterForm extends FormBase {
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'freak_chat_register';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];

    $form['password-repeat'] = [
      '#type' => 'password',
      '#title' => $this->t('Repeat Password'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Sign Up'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getValue('password') != $form_state->getValue('password-repeat')) {
      $form_state->setErrorByName(
        'password-repeat',
        $this->t('The password is different.'),
      );
    }

    $username = $form_state->getValue('username');
    $email = $form_state->getValue('email');

    if ($this->isUsernameExists($username)) {
      $form_state->setErrorByName('username', $this->t('This username is already taken.'));
    }

    if ($this->isEmailExists($email)) {
      $form_state->setErrorByName('email', $this->t('This email is already registered.'));
    }

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
    $user = User::create();
    $user->setUsername($form_state->getValue('name'));
    $user->setEmail($form_state->getValue('email'));
    $user->setPassword($form_state->getValue('password'));
    $user->enforceIsNew();
    $user->activate();
    $user->save();

    // Assign a role to the user.
    $role_name = 'freak_chat'; // Replace with the role's machine name.
    $role = Role::load($role_name);

    if ($role) {
      $user->addRole($role->id());
      $user->save();
    }

    // Optionally, log in the user after registration.
    user_login_finalize($user);

    // Redirect the user to a different page after registration.
    $form_state->setRedirect('user.page');
  }

  /**
   * Check if a username already exists.
   *
   * @param string $username
   *   The username to check.
   *
   * @return bool
   *   TRUE if the username exists, FALSE otherwise.
   */
  private function isUsernameExists($username) {
    $user_storage = $this->entityTypeManager->getStorage('user');
    $user = $user_storage->loadByProperties(['name' => $username]);
    return !empty($user);
  }

  /**
   * Check if an email already exists.
   *
   * @param string $email
   *   The email to check.
   *
   * @return bool
   *   TRUE if the email exists, FALSE otherwise.
   */
  private function isEmailExists($email) {
    $user_storage = $this->entityTypeManager->getStorage('user');
    $user = $user_storage->loadByProperties(['mail' => $email]);
    return !empty($user);
  }

}
