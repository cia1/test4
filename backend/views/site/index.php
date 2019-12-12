<?php

use yii\helpers\Html;
use yii\web\View;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use backend\models\Apple;
use backend\models\AppleActionForm;

/**
 * @var $this                      View
 * @var $provider                  ActiveDataProvider
 * @var $appleActionForm           AppleActionForm
 */
$this->title = 'Яблоки';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Яблоки</h1>
    </div>

    <div class="body-content">
        <div class="actions">
            <a href="<?= Url::toRoute(['site/create']) ?>" id="create-apple">Создать яблоки</a>
        </div>
        <?php try {
            echo GridView::widget([
                'dataProvider' => $provider,
                'columns' => [
                    [
                        'attribute' => 'color',
                        'value' => function (Apple $apple) {
                            return '<span class="color" style="background-color:#' . $apple->color . ';"></span>';
                        },
                        'format' => 'raw',
                    ],
                    'created_at',
                    [
                        'label' => 'Состояние',
                        'value' => function (Apple $apple) {
                            if ($apple->onTree === true) {
                                return 'На дереве';
                            } elseif ($apple->rotten === true) {
                                return '<strong>Сгнило</strong> ' . $apple->rotterPeriod() . ' назад (:';
                            } else {
                                return '<strong>Лежит на земле</strong> уже ' . $apple->fallPeriod();
                            }
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'eaten',
                        'value' => function (Apple $apple) {
                            return $apple->eaten . '%';
                        },
                    ],
                    [
                        'label' => '',
                        'value' => function (Apple $apple) {
                            return '
                            <span class="button" onclick="appleFall(' . $apple->id . ');">Сорвать</span>
                            <span class="button" onclick="appleEat(' . $apple->id . ');">Откусить</span>
                            <span class="button" onclick="appleDelete(' . $apple->id . ');">Выбросить</span>
                            ';
                        },
                        'format' => 'raw',
                    ],
                ],
            ]);
        } catch (Exception $e) {
        } ?>

    </div>


    <?php
    $appleActionForm->scenario = AppleActionForm::SCENARIO_CREATE;
    $form = ActiveForm::begin([
        'id' => 'apple-create',
        'action' => Url::toRoute('site/create'),
    ]); ?>
    <?= $form->field($appleActionForm, 'quantity')->textInput(['type' => 'number']) ?>
    <?= $form->field($appleActionForm, 'onGround')->checkbox() ?>
    <p>Если &laquo;сорвать несколько&raquo; установлен, то часть яблок будет сорвана и лежать на земле.</p>
    <div class="form-group">
        <?= Html::submitButton('Создать', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>


    <?php
    $appleActionForm->scenario = AppleActionForm::SCENARIO_FALL;
    $form = ActiveForm::begin([
        'id' => 'apple-fall',
        'action' => Url::toRoute('site/fall'),
        'enableAjaxValidation' => true,
    ]); ?>
    <?= $form->field($appleActionForm, 'id')->hiddenInput()->label(false) ?>
    <p>Яблоко будет сорвано и брошено на землю.</p>
    <div class="form-group">
        <?= Html::submitButton('Сорвать', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>


    <?php
    $appleActionForm->scenario = AppleActionForm::SCENARIO_EAT;
    $form = ActiveForm::begin([
        'id' => 'apple-eat',
        'action' => Url::toRoute('site/eat'),
        'enableAjaxValidation' => true,
    ]); ?>
    <?= $form->field($appleActionForm, 'id')->hiddenInput()->label(false) ?>
    <?= $form->field($appleActionForm, 'percent')->textInput(['type' => 'number']) ?>
    <div class="form-group">
        <?= Html::submitButton('Откусить', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>


    <?php
    $appleActionForm->scenario = AppleActionForm::SCENARIO_DELETE;
    $form = ActiveForm::begin([
        'id' => 'apple-delete',
        'action' => Url::toRoute('site/delete'),
        'enableAjaxValidation' => true,
    ]); ?>
    <?= $form->field($appleActionForm, 'id')->hiddenInput()->label(false) ?>
    <p>Действительно хотите убрать это яблоко?</p>
    <div class="form-group">
        <?= Html::submitButton('Выборосить', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
