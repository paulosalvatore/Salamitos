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

use Aura\Intl\Exception;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

set_time_limit(60 * 60);

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

    /**
     * Displays a view
     *
     * @param array ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display(...$path)
    {
        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }

        if ($page == "home")
		{
			$diretorioVideosEnviar = "videos_enviar/";
			$diretorioVideosEnviados = "videos_enviados/";
			$videosEnviar = scandir($diretorioVideosEnviar);

			$emailEnviado = false;
			$videoEncontrado = false;

			$tempoNecessario = 2 * 60;

			foreach ($videosEnviar as $json)
			{
				try {
					if ($json != "." && $json != ".." && strpos($json, ".json") !== false)
					{
						$video = str_replace(".json", ".mp4", $json);

						$criadoEm = filemtime($diretorioVideosEnviar . $video);

						$processar = ($criadoEm + $tempoNecessario) < time();

						if (!$processar)
							continue;

						$destino = json_decode(file_get_contents($diretorioVideosEnviar . $json), true);

						$dados = [
							"assunto" => "Salamitos no Rock in Rio",
							"corpo_email" => "Confira seu vídeo personalizado de Salamitos no Rock in Rio.",
							"video" => $diretorioVideosEnviar . $video,
							"email_destino" => $destino["email"],
							"nome_destino" => $destino["username"]
						];

						$email = $this->enviarEmail($dados);

						if ($email["dados"]["enviado"])
						{
							rename($diretorioVideosEnviar . $json, $diretorioVideosEnviados . $json);
							rename($diretorioVideosEnviar . $video, $diretorioVideosEnviados . $video);

							echo "Vídeo <b>" . $video . "</b> enviado para <b>" . $destino["username"] . "</b> (<b>" . $destino["email"] . "</b>).";

							$emailEnviado = true;
						}

						$videoEncontrado = true;

						break;
					}
				}
				catch (Exception $e) {
					//echo 'Erro encontrado: ',  $e->getMessage(), "<br>";
				}
			}

			if (!$videoEncontrado)
				echo 'Nenhum vídeo foi encontrado.';
			elseif (!$emailEnviado)
				echo 'Um vídeo foi encontrado mas o e-mail não foi enviado';

			echo '<meta http-equiv="refresh" content="1">';
		}

        $this->set(compact('page', 'subpage'));

        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }
}
