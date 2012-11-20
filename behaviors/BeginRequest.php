<?php

class BeginRequest extends CBehavior {

    // The attachEventHandler() mathod attaches an event handler to an event. 
    // So: onBeginRequest, the handleBeginRequest() method will be called.
    public function attach($owner) {
        $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginRequest'));
    }

    public function handleBeginRequest($event) {
        $app = Yii::app();
        if (isset($app->user))
            $user = $app->user;

        if (isset($_POST['language'])) {
            $app->language = $_POST['language'];
            $app->user->setState('language', $_POST['language']);
            $cookie = new CHttpCookie('language', $_POST['language']);
            $cookie->expire = time() + (31536000); // (1 year)
            Yii::app()->request->cookies['language'] = $cookie;
        } else if ($app->user->hasState('language'))
            $app->language = $app->user->getState('language');
        else if (isset(Yii::app()->request->cookies['language']))
            $app->language = Yii::app()->request->cookies['language']->value;
    }

}