<?php

    //セッション名
    session_name('sesname');
    //セッションの使用宣言
    session_start();
    //セッションのセキュリティ機能
    session_regenerate_id(true);
    
    //書き込みファイル名
    $savefile="log/questionnaire.txt";

    //エラー表示用変数の初期化
    if(isset($_SESSION["htmlError"])==false){
        $_SESSION["htmlError"]="";
    }

    //名前用変数の初期化
    if(isset($_SESSION['name'])==false){
        $_SESSION['name']="";
    }
    //メール用変数の初期化
    if(isset($_SESSION['name'])==false){
        $_SESSION['name']="";
    }
    //年齢用変数の初期化
    if(isset($_SESSION['name'])==false){
        $_SESSION['name']="";
    }
    //性別用変数の初期化
    if(isset($_SESSION["gender"])==false){
        $_SESSION["gender"]="other";
        $_SESSION["sex"]="";
    }
    //可愛いもの用変数の初期化
    if(isset($_SESSION['name'])==false){
        $_SESSION['name']="";
    }
    //一言用変数の初期化
    if(isset($_SESSION['name'])==false){
        $_SESSION['name']="";
    }
    //イメージタグ用変数
    if(isset($_SESSION['imgFile'])==false){
        $_SESSION['imgFile']="";
    }

    //変数初期化
    $mes=array();
    //既にファイルがあれば読み込む
    if(file_exists($savefile))
    {
        $mes=file($savefile);
    }

    //postかどうかチェックするやつ
    if($_SERVER["REQUEST_METHOD"]=="POST"){

        //欄が選択されてるかチェック
        if(isset($_POST['name'])&&isset($_POST['mail'])&&isset($_POST['age']))
        {
            //imageやるとこ
            if(isset($_SESSION["ipush"])){
                $ipushTemp=$_SESSION["ipush"];
            }
            $_SESSION=$_POST;
            $_SESSION["ipush"]=$ipushTemp;
            
            $OnceFile=$_FILES['image'];

            if($OnceFile['name']!=''){
        
                //ファイル名作成
                $NameTemp=md5($OnceFile['name'].microtime());//ユニークな英数字にする場合(こちらのほうが被らなくて良い)
        
                //アップロードされたファイルを指定されたパスと名前で保存
                move_uploaded_file($OnceFile['tmp_name'], 'img/'.$NameTemp);

                $_SESSION["ipush"]="img/".$NameTemp;
            }
            $_SESSION['imgFile']='<img src="'.$_SESSION["ipush"].'">';

            //必須項目チェック
            if($_POST['name']=="")
            {
                $_SESSION["htmlError"].="＊名前が入力されていません。<br>";
            }
            if($_POST['mail']=="")
            {
                $_SESSION["htmlError"].="＊メールアドレスが入力されていません。<br>";
            }
            else if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\.+_-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST["mail"])==false)
            {
                $_SESSION["htmlError"].="＊メールアドレスが不正です。<br>";
            }
            //表示部分
            foreach($mes as $key=>$val)
            {
                //名を整える
                $afVal=trim($val);
                //一文を指定した文字で区切って配列にしてくれる
                $moji=explode("<>",$afVal);

                if($moji[1]==$_POST["mail"])
                {
                    $_SESSION["htmlError"].="＊同一のメールアドレスの回答が既に存在しています。<br>";
                }
            }
            if($_POST['age']=="")
            {
                $_SESSION["htmlError"].="＊年齢が入力されていません。<br>";
            }
            else if(is_numeric($_POST["age"])==false)
            {
                $_SESSION["htmlError"].="＊年齢は半角で入力してください。<br>";
            }
        }

        $param="";
        if(isset($_POST["back"])){
            $param="";
        }
        else if(isset($_POST["conf"])){
            $param="?chk=1";
        }  
        else if(isset($_POST["fin"])){
            $param="?fin=1";
        }

        header("Location: " . $_SERVER['PHP_SELF'].$param);
        exit();
    }

    //ラジオボタン
    switch($_SESSION["gender"]){
        case "man":
            $_SESSION["manCheck"]="checked";
            $_SESSION["sex"]="男";
            break;
        case "woman":
            $_SESSION["womanCheck"]="checked";
            $_SESSION["sex"]="女";
            break;
        case "other":
            $_SESSION["otherCheck"]="checked";
            $_SESSION["sex"]="その他";
            break;
    }

    //チェックボックス
    if(isset($_SESSION["phone"])){
        $_SESSION["phoneSum"]=implode(" , ",$_SESSION["phone"]);
        foreach($_SESSION["phone"] as $keyChe=>$valChe){
            $_SESSION[$valChe."Check"]="checked";
        }
    }

    //読み込みテンプレート名
    if(isset($_GET["chk"])&&$_SESSION["htmlError"]==""){
        $templatefile="confirm.html";
    }
    else if(isset($_GET["fin"])&&$_SESSION["htmlError"]==""){

        //[\n]を[<br>]に置き換え
        $enter=str_replace(array("\r\n","\r","\n"),'<br>',htmlspecialchars($_SESSION['mess']));
    
        //GETで受け取った文字と改行タグを追加
        $mes[].=htmlspecialchars($_SESSION['name'])."<>".htmlspecialchars($_SESSION['mail'])."<>".htmlspecialchars($_SESSION['age'])."<>".htmlspecialchars($_SESSION['sex'])."<>".htmlspecialchars($_SESSION['cute'])."<>".htmlspecialchars($_SESSION['mess'])."\n";
    
        //ファイルへ保存
        file_put_contents($savefile,$mes);

        $_SESSION=array();

        $_SESSION["htmlError"]="更新されました。";

        $templatefile="finish.html";
    }
    else{
        $templatefile="question.html";
    }

    $HtmlData=file_get_contents($templatefile);

    $HtmlData=str_replace('{{error}}',$_SESSION["htmlError"],$HtmlData);
    $HtmlData=str_replace('{{name}}',$_SESSION['name'],$HtmlData);
    $HtmlData=str_replace('{{mail}}',$_SESSION['mail'],$HtmlData);
    $HtmlData=str_replace('{{age}}',$_SESSION['age'],$HtmlData);
    $HtmlData=str_replace('{{manCheck}}',$_SESSION['manCheck'],$HtmlData);
    $HtmlData=str_replace('{{womanCheck}}',$_SESSION['womanCheck'],$HtmlData);
    $HtmlData=str_replace('{{otherCheck}}',$_SESSION['otherCheck'],$HtmlData);
    $HtmlData=str_replace('{{gender}}',$_SESSION['sex'],$HtmlData);
    $HtmlData=str_replace('{{cute}}',$_SESSION['cute'],$HtmlData);
    $HtmlData=str_replace('{{iPhoneCheck}}',$_SESSION['iPhoneCheck'],$HtmlData);
    $HtmlData=str_replace('{{androidCheck}}',$_SESSION['androidCheck'],$HtmlData);
    $HtmlData=str_replace('{{blackberryCheck}}',$_SESSION['blackberryCheck'],$HtmlData);
    $HtmlData=str_replace('{{windowsphoneCheck}}',$_SESSION['windowsphoneCheck'],$HtmlData);
    $HtmlData=str_replace('{{phone}}',$_SESSION['phoneSum'],$HtmlData);
    $HtmlData=str_replace('{{mess}}',$_SESSION['mess'],$HtmlData);
    $HtmlData=str_replace('{{image}}',$_SESSION['imgFile'],$HtmlData);


    //正規表現で残った{{何か}}を消す
    $HtmlData=preg_replace("/{{.*?}}/","",$HtmlData);

    echo $HtmlData;

    /*
    
    ラジオボタン，チェックリストの持ち越し，sessionよろしく
    
    */
    


?>

