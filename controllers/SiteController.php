<?php

namespace app\controllers;

use app\models\Article;
use app\models\Category;
use app\models\CommentForm;
use app\models\SignupForm;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Article::find();
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 3]);
        $articles = $query->offset($pagination->offset)->limit($pagination->limit)->all();

        $popular = Article::find()->orderBy('viewed DESC')->limit(3)->all();
        $recent = Article::find()->orderBy('date ASC')->limit(3)->all();
        $categories = Category::find()->all();

        return $this->render('index', [
            'articles'=>$articles,
            'pagination'=>$pagination,
            'popular'=>$popular,
            'recent'=>$recent,
            'categories'=>$categories,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionSignup()
    {
        $model = new SignupForm();

        if(Yii::$app->request->isPost)
        {
            $model->load(Yii::$app->request->post());
            if($model->signup())
            {
                return $this->redirect(['login']);
            }
        }

        return $this->render('signup', ['model'=>$model]);
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionCategory($id)
    {
        $query = Article::find()->where(['category_id' => $id]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 6]);
        $articles = $query->offset($pagination->offset)->limit($pagination->limit)->all();

        $data['articles'] = $articles;
        $data['pagination'] = $pagination;

        $popular = Article::find()->orderBy('viewed DESC')->limit(3)->all();
        $recent = Article::find()->orderBy('date ASC')->limit(3)->all();
        $categories = Category::find()->all();

        return $this->render('category', [
            'articles'=>$data['articles'],
            'pagination'=>$data['pagination'],
            'popular'=>$popular,
            'recent'=>$recent,
            'categories'=>$categories,
        ]);
    }

    public function actionPost($id)
    {
        $article = Article::findOne($id);
        $popular = Article::find()->orderBy('viewed DESC')->limit(3)->all();
        $recent = Article::find()->orderBy('date ASC')->limit(3)->all();
        $categories = Category::find()->all();
        $comments = $article->getAllComments();
        $commentForm = new CommentForm();

        return $this->render('post', [
            'article' => $article,
            'popular'=>$popular,
            'recent'=>$recent,
            'categories'=>$categories,
            'comments'=>$comments,
            'commentForm'=>$commentForm
        ]);
    }

    public function actionComment($id)
    {
        $model = new CommentForm();

        if (Yii::$app->request->isPost)
        {
            $model->load(Yii::$app->request->post());
            if ($model->saveComment($id))
            {
                return $this->redirect(['site/post','id'=>$id]);
            }
        }
    }
}
