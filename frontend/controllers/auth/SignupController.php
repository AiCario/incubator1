<?php

namespace frontend\controllers\auth;

use portfolio\repositories\UserRepository;
use portfolio\useCases\auth\SignupService;
use Yii;
use portfolio\forms\auth\SignupForm;
use yii\base\Module;
use yii\web\Controller;
use DomainException;

class SignupController extends Controller
{
    public $layout = 'user';

    private $service;
    private $users;

    public function __construct(string $id, Module $module, SignupService $service, UserRepository $users, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->users = $users;
    }

    public function actionRequest()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $form = new SignupForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->signup($form);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Проверьте свою почту для подтверждения регистрации'));
                return $this->goHome();
            } catch (DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('request', [
            'model' => $form,
        ]);
    }

    public function actionConfirm($token)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        try {
            $this->service->confirm($token);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Ваш аккаунт была подтвержден'));
            return $this->redirect(['/' . Yii::$app->language . '/user/login']);
        } catch (DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->goHome();
    }
}