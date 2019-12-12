<?php

namespace backend\controllers;

use backend\models\AppleActionForm;
use backend\models\Apple;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\web\Response;
use yii\db\StaleObjectException;
use Throwable;
use yii\base\InvalidConfigException;

/**
 * `GET index` - таблица со списком яблок
 * `POST create` - добавление яблок
 * `POST fall` - сорвать яблоко
 * `POST eat` - откусить кусочек от яблока
 * `POST delete` - выбросить (удалить) яблоко
 */
class SiteController extends Controller
{
    public const PAGE_SIZE = 20; //Количество элементов (яблок) на странице

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'create', 'fall', 'eat', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'fall' => ['post'],
                    'eat' => ['post'],
                    'delete' => ['post'],
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /** @inheritDoc */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Таблица со списком яблок
     *
     * @return string
     */
    public function actionIndex()
    {
        $provider = new ActiveDataProvider([
            'query' => Apple::find(),
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
        ]);
        return $this->render('index', [
            'provider' => $provider,
            'apple' => new Apple(),
            'appleActionForm' => new AppleActionForm(),
        ]);
    }

    /**
     * Добавление яблок (POST)
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionCreate()
    {
        $form = self::_form(AppleActionForm::SCENARIO_CREATE);
        $quantity = $form->create();
        if ($form->hasErrors()) {
            Yii::$app->session->setFlash('error', $form->getErrorSummary(false));
        } else {
            Yii::$app->session->setFlash(
                'success',
                Yii::$app->i18n->format('{n, plural, =0{Яблоки не добавлены} =1{1 яблоко добавлено} one{# яблок добавлено} few{# яблока добавлено} many{# яблок добавлено} other{# яблок добавлено}}.',
                    ['n' => $quantity], 'ru_RU')
            );
        }
        $this->redirect(Url::toRoute('site/index'));
    }

    /**
     * Сорвать (уронить) яблоко (POST)
     *
     * @return array|null
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionFall()
    {
        $form = self::_form(AppleActionForm::SCENARIO_FALL);
        $form->fall();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $form->summaryAjaxErrors();
        }
        if ($form->hasErrors() === true) {
            Yii::$app->session->setFlash('error', $form->getErrorSummary(false));
        } else {
            $form->model->save(false); //валидация уже пройдена
            Yii::$app->session->setFlash('success', 'Яблоко сорвано и теперь лежит на земле');
        }
        $this->redirect(Url::toRoute('site/index'));
        return null;
    }

    /**
     * Откусить яблоко
     *
     * @return array|null
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionEat()
    {
        $form = self::_form(AppleActionForm::SCENARIO_EAT);
        $form->eat();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $form->summaryAjaxErrors();
        }
        if ($form->hasErrors() === true) {
            Yii::$app->session->setFlash('error', $form->getErrorSummary(false));
        } else {
            $form->model->save(false); //валидация уже пройдена
            if ($form->model->eaten === 100) {
                $message = 'Яблоко полностью съедено и его больше нет';
            } else {
                $message = 'Откушен кусочек яблока, осталось ' . $form->model->size . '%';
            }
            Yii::$app->session->setFlash('success', $message);
        }
        $this->redirect(Url::toRoute('site/index'));
        return null;
    }

    /**
     * Удалить (выбросить) яблоко (POST)
     *
     * @return array|null
     * @throws StaleObjectException
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionDelete()
    {
        $form = self::_form(AppleActionForm::SCENARIO_DELETE);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $form->summaryAjaxErrors();
        }
        if ($form->delete()) {
            Yii::$app->session->setFlash('success', 'Яблоко выборошено, его больше нет');
        } else {
            Yii::$app->session->setFlash('error', $form->getErrorSummary(false));
        }
        $this->redirect(Url::toRoute('site/index'));
        return null;
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    /**
     * Инициирует класс формы
     *
     * @param string $scenario
     * @return AppleActionForm
     */
    private static function _form(string $scenario): AppleActionForm
    {
        $form = new AppleActionForm([
            'scenario' => $scenario,
        ]);
        $form->load(Yii::$app->request->post());
        return $form;
    }

}
