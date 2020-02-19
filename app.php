<?php
    use GuzzleHttp\Client;

    require __DIR__.'/vendor/autoload.php';

    require __DIR__.'/Db.php';
    require __DIR__.'/User.php';

    $config = require __DIR__.'/config.php';



    use Telegram\Bot\Api;

    $telegram = new Api($config['BOT_TOKEN']);



    $result = $telegram->getWebhookUpdates();

    if (isset($result["message"]["text"])) {

      $text = $result['message']['text']; // Переменная с текстом сообщения
      $chat_id = $result['message']['chat']['id']; // Чат ID пользователя
      $first_name = $result['message']['chat']['first_name']; //Имя пользователя
      $user_id = $result['message']['from']['id']; //Имя пользователя
      $username = $result['message']['chat']['username']; //Юзернейм пользовате

      $db = new Db($config['db']);
      $User = new User($user_id, $db);


      if (!$User->isSet()){
        if (!$User->set()){
          $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Не удалось зарегистрировать вас в нашей базе! Попробуйте снова или обратитесь к администратору"]);
          exit();
        }
      }


      $Menu = new Menues($User);
      $req = $Menu->action(mb_strtolower($text));

      if (isset($req['keyboard']))
        $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $req['keyboard'], 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
      else
        $reply_markup = "";

      $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $req['reply'], 'reply_markup' => $reply_markup, 'parse_mode' => "HTML"]);


    }
