<?php

namespace frontend\controllers;

use portfolio\forms\ContactForm;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use portfolio\useCases\ContactService;
use Yii;
use DomainException;

class ContactController extends Controller
{
    public $layout = 'contact';

    private $service;

    public function __construct(string $id, Module $module, ContactService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    public function actionIndex()
    {
        $form = new ContactForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->send($form);
                Yii::$app->session->setFlash('success', 'Сообщение было успешно отправлено');
                return $this->refresh();
            } catch (DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('index', [
            'model' => $form
        ]);
    }
}