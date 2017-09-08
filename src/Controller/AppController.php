<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Network\Exception\NotAcceptableException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        //$this->loadComponent('Csrf');
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        // Note: These defaults are just to get started quickly with development
        // and should not be used in production. You should instead set "_serialize"
        // in each action as required.
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

	public function enviarEmail($dados)
	{
		if (!isset($dados["corpo_email"]))
			throw new NotAcceptableException();

		$dadosPadroes = [
			"email_remetente" => "smtp@tgroups.com.br",
			"nome_remetente" => "Salamitos - Rock In Rio",
			"email_destino" => "paulo.salvatore@tgroups.com.br",
			"nome_destino" => "Paulo Salvatore",
			"assunto" => "Assunto"
		];

		foreach ($dadosPadroes as $chave => $valor)
			if (!isset($dados[$chave]))
				$dados[$chave] = $valor;

		//$dados["corpo_email"] .= 'Mensagem enviada em ' . Time::now() . '.';

		$mail = new PHPMailer();

		//$mail->SMTPDebug = 2;

		$mail->isSMTP();
		$mail->Host = "mail.tgroups.com.br";
		$mail->SMTPAuth = true;
		$mail->Username = "smtp@tgroups.com.br";
		$mail->Password = "nova@2017";
		$mail->SMTPAutoTLS = false;
		$mail->Port = 587;
		$mail->CharSet = "UTF-8";

		$mail->setFrom($dados["email_remetente"], $dados["nome_remetente"]);
		$mail->addReplyTo($dados["email_remetente"], $dados["nome_remetente"]);
		$mail->addAddress($dados["email_destino"], $dados["nome_destino"]);

		$mail->isHTML(true);

		$mail->Subject = $dados["assunto"];

		$mail->Body = $dados["corpo_email"];

		$mail->addAttachment($dados["video"]);

		$enviarEmail = $mail->send();

		$dados["enviado"] = $enviarEmail;

		return [
			"mail" => $mail,
			"dados" => $dados
		];
	}
}
