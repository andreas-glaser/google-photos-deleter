<?php

namespace AndreasGlaser\GooglePhotosDeleter;

use AndreasGlaser\Helpers\ArrayHelper;
use AndreasGlaser\Helpers\Validate\Expect;
use AndreasGlaser\Helpers\View\PHPView;
use Google_Client;

/**
 * Class GooglePhotosDeleter
 *
 * @package AndreasGlaser\GooglePhotosDeleter
 * @author  Andreas Glaser
 */
class GooglePhotosDeleter
{
    /**
     * @var \Google_Client
     */
    protected $client;

    /**
     * GooglePhotosDeleter constructor.
     *
     * @author Andreas Glaser
     */
    public function __construct()
    {
        session_start();
        $config = parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.ini', true);

        if (!isset($_SESSION['access_token'])) {
            $_SESSION['access_token'] = null;
        }

        $this->client = new Google_Client();
        $this->client->setApplicationName('Google Photos Deleter');
        $this->client->setDeveloperKey($config['credentials']['developer_key']);
        $this->client->setClientId($config['oauth']['client_id']);
        $this->client->setClientSecret($config['oauth']['client_secret']);
        $this->client->addScope('https://picasaweb.google.com/data/');

        if ($_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
        }

        $redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . '/auth';
        $this->client->setRedirectUri($redirectUri);

        PHPView::setGlobal('hasAccessToken', !$this->client->isAccessTokenExpired());
        PHPView::setGlobal('authUrl', !$this->client->createAuthUrl());
    }

    /**
     * Simple router
     *
     * @param $uri
     *
     * @author Andreas Glaser
     */
    public function dispatchRequest($uri)
    {
        switch ($uri) {
            case '/auth':
                $result = $this->authAction();
                break;
            case '/delete':
                $result = $this->deleteAction();
                break;
            default:
                $result = $this->indexAction();
        }

        if (!is_array($result)) {
            throw new \LogicException('Action has to return a data array');
        }

        $this->renderHtml($result);
    }

    /**
     * @return array
     * @author Andreas Glaser
     */
    protected function authAction()
    {
        if (isset($_GET['code'])) {
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            $this->client->setAccessToken($token);
            $_SESSION['access_token'] = $token;
        }

        $this->redirect('/');

        return [
            'title' => 'Google Photos Deleter | Authentication',
        ];
    }

    /**
     * @param string $dest
     *
     * @author Andreas Glaser
     */
    private function redirect($dest)
    {
        Expect::str($dest);
        header(sprintf('Location: %s', $dest));
        exit;
    }

    /**
     * @return array
     * @author Andreas Glaser
     */
    protected function deleteAction()
    {
        if ($this->client->isAccessTokenExpired()) {
            $this->redirect('/');
        }

        $httpClient = $this->client->getHttpClient();

        $response = $httpClient->request('GET', 'https://picasaweb.google.com/data/feed/api/user/default?showall', [
            'headers' => [
                'GData-Version', '2',
                'Authorization' => 'Bearer ' . $_SESSION['access_token']['access_token'],
            ],
        ]);

        $xml = simplexml_load_string($response->getBody()->getContents());
        $json = json_encode($xml);
        $albums = json_decode($json, true);

        if (isset($albums['entry']) && !empty($albums['entry'])) {
            $albums = ArrayHelper::isAssoc($albums['entry']) ? [$albums['entry']] : $albums['entry'];

            if (isset($_GET['confirm'])) {

                foreach ($albums AS $album) {

                    if ($album['title'] === 'Auto Backup') {
                        continue;
                    }

                    $response = $httpClient->request('DELETE', $album['id'], [
                        'headers' => [
                            'GData-Version', '2',
                            'Authorization' => 'Bearer ' . $_SESSION['access_token']['access_token'],
                            'If-Match'      => '*',
                        ],
                    ]);
                }
            }
        } else {
            $albums = [];
        }

        return [
            'body'  => $this->view('delete.html.php', ['albums' => $albums]),
            'title' => 'Google Photos Deleter | Delete',
        ];
    }

    /**
     * @param string $path
     * @param array  $data
     *
     * @return \AndreasGlaser\Helpers\View\PHPView
     * @author Andreas Glaser
     */
    protected function view($path, array $data = [])
    {
        Expect::str($path);

        return new PHPView(implode(DIRECTORY_SEPARATOR, [__DIR__, 'Resources', 'Views', $path]), $data);
    }

    /**
     * @return array
     * @author Andreas Glaser
     */
    protected function indexAction()
    {
        return [
            'body'  => $this->view('index.html.php'),
            'title' => 'Google Photos Deleter',
        ];
    }

    /**
     * @param array $viewData
     *
     * @author Andreas Glaser
     */
    protected function renderHtml(array $viewData = [])
    {
        if (!isset($viewData['title'])) {
            $viewData['title'] = 'Google Photos Deleter';
        }

        if (!isset($viewData['body'])) {
            $viewData['body'] = 'UNDEFINED';
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache');

        $view = $this->view('layout.html.php', $viewData);

        echo $view->render();
    }
}