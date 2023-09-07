<?php declare(strict_types = 1);

namespace Drupal\freak_chat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Freak Chat form.
 */
final class LoginForm extends FormBase {

  protected $currentUser;
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'freak_chat_login';
  }

  public function __construct(AccountProxyInterface $current_user, RequestStack $request_stack) {
    $this->currentUser = $current_user;
    $this->requestStack = $request_stack;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('request_stack')
    );
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

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Log In'),
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
    $username = $form_state->getValue('username');
    $password = $form_state->getValue('password');

    $uid = \Drupal::service('user.auth')
      ->authenticate($username, $password);

    if ($uid) {
      $user = \Drupal\user\Entity\User::load($uid);
      // User authentication succeeded. Log in the user.
      if ($user) {
        // Log in the user.
        user_login_finalize($user);
        $this->messenger()->addStatus($this->t('Logged in successfully.'));
        $form_state->setRedirect('user.login');
      }
    }
    else {
      // User authentication failed.
      $form_state->setErrorByName(
        $this->t('User or password is incorrect.'),
      );
    }
  }

}
