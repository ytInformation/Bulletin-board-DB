<?php
    //データベース接続
    $dsn = 'データベース名';
    $user = 'ユーザ名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    //データベース内にテーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS tbtest"
        . " ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "comment TEXT,"
        //パスワード
        . "pass TEXT,"
        //時間
        . "time DATETIME"
        . ");";
    $stmt = $pdo->query($sql);

    //データベースのテーブル一覧表示
    $sql = 'SHOW TABLES';
    $result = $pdo->query($sql);
    foreach ($result as $row) {
        echo $row[0];
        echo '<br>';
    }
    echo "<hr>";
    
    if (!empty($_POST["name"]) && !empty($_POST["comment"])
        && !empty($_POST["passwordNew"]) && empty($_POST["numberEdit"])) {

        // ここで内容登録 カラム　insert分を準備
        $sql = $pdo->prepare("INSERT INTO tbtest (name, comment, pass,time) 
            VALUES (:name, :comment, :pass, :time);");
        //変数をパラメータにバインド
        $sql->bindParam(':name', $name, PDO::PARAM_STR);
        $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
        $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
        $sql->bindParam(':time', $time, PDO::PARAM_STR);
    
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $pass = $_POST["passwordNew"];
        $time = date("Y/m/d G:i:s");
        $sql->execute();//sqlに実行

        //削除　削除番号のname="Delnumber"が送信されたとき
    } else if (!empty($_POST["Delnumber"])) {
    //$delete = $_POST["number"];

    //削除パスワードの取得
    $id = $_POST["Delnumber"];//idに格納
    $sql = 'SELECT * FROM tbtest WHERE id=:id ';
    $stmt = $pdo->prepare($sql);                  //prepareメソッドでSQLをセット
    $stmt->bindParam(':id', $id, PDO::PARAM_INT); //変数をセット
    $stmt->execute();                             //SQLを実行
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        // $rowにはテーブルのカラム名が入る
    }
    
    $passDel = $row[3];

    //パスワードの照合
    if ($_POST["passwordDel"] == $passDel) {
        $id = $_POST["Delnumber"];
        $sql = 'delete from tbtest where id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
    }else {
        echo "パスワードが一致していません！<br><br>";
    }


    //編集が送信されたとき
    } else if (!empty($_POST["edit"])) {
        
        //編集番号取得
        $id = $_POST["edit"];
        $sql = 'SELECT * FROM tbtest WHERE id=:id'; //
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();//全ての結果行を含む配列を返す
        //$rowの添字（[ ]内）は、4-2で作成したカラムの名称に併せる必要があります。
        foreach ($results as $row) {
        }
        
        // 代入
    $passEdi = $row[3];

    //パスワードの照合
    if ($_POST["passwordEdi"] == $passEdi) {
    
        $id =  $_POST["edit"]; // idがこの値のデータだけを抽出したい、とする
        $sql = 'SELECT * FROM tbtest WHERE id=:id ';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                             // ←SQLを実行する。
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
            //$rowの中にはテーブルのカラム名が入る
            $hiddenNumber = $row['id'];
            $nameEdit = $row['name'];
            $commetEdit = $row['comment'];
            // パスワードを送る 違う
            $passEdit = $row['pass'];
        }
    } else {
        echo "パスワードが一致していません！<br><br>";
    }
    
    //怪しい
    } else if (!empty($_POST["name"]) && !empty($_POST["comment"])
        && !empty($_POST["passwordNew"]) && !empty($_POST["numberEdit"])) {
    // 編集機能
    // 名前かつコメントかつhiddenの編集番号が入ってたらif文通る
    // パスワード
    $pass = $_POST["passwordNew"]; //変更パスワード

    $id = $_POST["numberEdit"]; //変更する投稿番号

    $name = $_POST["name"];
    $comment = $_POST["comment"];
    $postTime = date("Y/m/d G:i:s");
    $time = $postTime; //変更時間 　// スペースもエラーとして出てくる！

    $sql = 'UPDATE tbtest SET name=:name,comment=:comment,time=:time
            ,pass=:pass WHERE id=:id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
} else {
    echo "名前とコメント両方入力してください<br><br>";
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>m5-01</title>
</head>

<body>
    <form method="POST">
        <!--新規投稿、編集-->
        お名前： <input type="text" name="name"
            value=<?php if (!empty($nameEdit)) {echo $nameEdit;} ?>><br>
            
        コメント： <input type="text" name="comment"
            value=<?php if (!empty($commetEdit)) {echo $commetEdit;} ?>><br>
            
        パスワード： <input type="password" name="passwordNew" placeholder="パスワード"
            value=<?php if (!empty($passEdit)) {echo $passEdit;} ?>><br>

        <!--編集（隠れている）編集番号:   nameは目印-->
        <input type="hidden" name="numberEdit"
            value=<?php if (!empty($hiddenNumber)) {echo $hiddenNumber;} ?>><br>
        <!--$nameEditが定義されたらvalueに値が入る(上の名前、コメントも同様)-->
        <input type="submit" name="submit" value="送信"><br><br>

        <!--削除-->
        削除番号: <input type="number" name="Delnumber"><br>
        パスワード： <input type="password" name="passwordDel" placeholder="パスワード"><br>
        <input type="submit" name="submit" value="削除"><br><br>

        <!--編集-->
        編集番号: <input type="number" name="edit"><br>
        パスワード：<input type="password" name="passwordEdi" placeholder="パスワード"><br>
        <input type="submit" name="submit" value="編集">
    </form>
</body>

</html>
<br>

<?php
//m4-06 入力したデータレコードを抽出し表示する
//$sql = 'SELECT * FROM tbtest WHERE id='.$id;　絞り込んで選ぶ
$sql = 'SELECT * FROM tbtest'; //★
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
//$rowの添字（[ ]内）は、4-2で作成したカラムの名称に併せる必要があります。
foreach ($results as $row) {
    //$rowの中にはテーブルのカラム名が入る
    echo $row['id'] . ',';//ここの区切りはわかりやすくするためのもの
    echo $row['name'] . ',';
    echo $row['comment'] . ',';
    echo $row['pass'] . ',';
    echo $row['time'] . '<br>';
    
}
      echo "<hr>";
?>