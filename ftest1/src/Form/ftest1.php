<?php

namespace Drupal\ftest1\Form;

use Drupal\Core\Form\FormBase; // Базовый класс Form API
use Drupal\Core\Form\FormStateInterface; // Класс отвечает за обработку данных

/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */

class Ftest1 extends FormBase {

	// метод, который отвечает за саму форму - кнопки, поля
	public function buildForm(array $form, FormStateInterface $form_state) {
		//First Name
		$form['first_name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('First Name :'),
			'#description' => $this->t('Имя не должно содержать цифр'), // только если Вы не сын Илона Маска :)
			'#required' => TRUE,
		];
		//Last Name
		$form['second_name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Last Name :'),
			'#description' => $this->t('Фамилия не должна содержать цифр'),
			'#required' => TRUE,
		];
		//Subject
		$form['subject'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Subject :'),
			'#description' => $this->t('Укажите тезисно тему Вашего сообщения (не более 55 символов)'),
			'#required' => TRUE,
		];
		//Message
		$form['mess'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Message'),
			'#description' => $this->t('Введите Ваше сообщение'),
			'#required' => TRUE,
		];
		// Email
		$form['email_to'] = [
			'#type' => 'textfield',
			'#title' => $this->t('E-mail'),
			'#description' => $this->t('Укажите почту получателя'),
			'#required' => TRUE,
		];
		// кнопка
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Отправить форму'),
		];

		return $form;
	}

	public function getFormId() {
		return 'Форма для отправка сообщения';
	}

	public function validateForm(array &$form, FormStateInterface $form_state) {
		// имя
		$first_name = $form_state->getValue('first_name');
		$first_name_is_number = preg_match("/[\d]+/", $first_name, $match);

		if ($first_name_is_number > 0) {
			$form_state->setErrorByName('first_name', $this->t('Имя содержит цифру.'));
		}
		// фамилия
		$second_name = $form_state->getValue('second_name');
		$second_name_is_number = preg_match("/[\d]+/", $second_name, $match);

		if ($second_name_is_number > 0) {
			$form_state->setErrorByName('second_name', $this->t('Фамилия содержит цифру.'));
		}
		// тема
		$subject = $form_state->getValue('subject');

		if (strlen($subject) > 55){
			$form_state->setErrorByName('subject', $this->t('Тема может содержать не более 55 символов.'));
		}

		// сообщение
		$mess = $form_state->getValue('mess');

		if (strlen($subject) == 0){
			$form_state->setErrorByName('mess', $this->t('Сообщение не может быть пустым.'));
		}

		// почта получателя
		$email_to = $form_state->getValue('email_to');

		if (!filter_var($email_to, FILTER_VALIDATE_EMAIL)){
			$form_state->setErrorByName('email_to', $this->t('Данная почта не существует.'));
		}
	}
	// действия по сабмиту
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$email = $form_state->getValue('email_to');
		$subject = $form_state->getValue('subject');
		$message = $form_state->getValue('mess');
		$firs_tname = $form_state->getValue('first_name');
		$last_name = $form_state->getValue('second_name');
		$message = wordwrap($message, 70, "\r\n");


		if (mail($email, $subject, $message)){
			drupal_set_message(t('Сообщение успешно отправлено'));
		}
		else{
			drupal_set_message(t('Ошибка при отправке сообщения'));
		}

		$arr = array(
		 'properties' => array(
			 array(
						 'property' => 'email',
						 'value' => $email
					 ),
					 array(
						 'property' => 'firstname',
						 'value' => $firs_tname
					 ),
					 array(
						 'property' => 'lastname',
						 'value' => $las_tname
					 )
				)
		 );
		 $post_json = json_encode($arr);
		 //$hapikey = readline("f79b3107-06e8-4c86-87b9-485a8478db51");
		 $endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey=f79b3107-06e8-4c86-87b9-485a8478db51';

		 $ch = @curl_init();
		 @curl_setopt($ch, CURLOPT_POST, true);
		 @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
		 @curl_setopt($ch, CURLOPT_URL, $endpoint);
		 @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 $response = @curl_exec($ch);
		 $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
		 $curl_errors = curl_error($ch);
		 @curl_close($ch);
		 //echo "curl Errors: " . $curl_errors;
		 //echo "\nStatus code: " . $status_code;
		 //echo "\nResponse: " . $response;
		 if ($status_code == 200){
			 drupal_set_message(t('Пользователь успешно добавлен.'));
		 }
		 if ($status_code == 409){
			 drupal_set_message(t('Пользователь уже существует.'));
		 }
		 if ($status_code == 400){
			 drupal_set_message(t('При регистрации допущены ошибки в веденных данных.'));
		 }
		 if ($status_code == 401){
			 drupal_set_message(t('Неизвестная ошибка !!!'));
		 }


	}
}
