// -が押されたら入力ボックスを減らす関数
function removeChildNodes(obj, rmObj) {
    // 親要素の取得
    const parent = document.getElementById(obj);
    // 親要素内の子要素が 2 以上なら指定された子要素を削除できる
    if (parent.childElementCount > 1) {
        // 指定された子要素の削除
        document.getElementById(rmObj).remove();
    }
}

// +が押されたら入力ボックスを増やす関数
function addChildNodes(obj) {
    // 親要素の取得
    const parent = document.getElementById(obj);
    //console.log(id, parent);  // 確認用

    // 追加する要素の生成
    // ラベル
    const label = document.createElement("div");
    // spanタグ
    const elm1Label = document.createElement("span");

    // 指定された要素により処理を分岐
    if (obj === "phone_list") {
        // 連絡先の場合
        // ダミーデータの生成
        var tel = "08011112222";


        // 削除用に使うIDの生成
        const rmId = "phone" + (parseInt(parent.childElementCount) + 1);

        // 追加する要素の生成
        // ラベルのIDをセット
        label.setAttribute("id", rmId)
        elm1Label.innerHTML = " 　区分：";  // 「　区分：」を表示するspan要素

        // 連絡先の入力テキストボックス
        const child = document.createElement("input");
        child.setAttribute("value", tel);  // ダミーデータ
        child.setAttribute("type", "text");
        child.setAttribute("name", "PHONE[]");
        child.setAttribute("size", "10");

        // 区分のセレクトボックス
        const classId = document.createElement("select");
        classId.setAttribute("name", "PHONECLASS[]");
        for (i = 0; i < phoneClasses.length; i++) {
            const classIdChild = document.createElement("option");
            classIdChild.setAttribute("value", phoneClassesId[i]);
            classIdChild.innerHTML = phoneClasses[i];
            classId.append(classIdChild);
        }

        // 削除用リンク
        const rmlink = document.createElement("button");
        rmlink.setAttribute("class", "btn");
        rmlink.setAttribute("type", "button");
        rmlink.setAttribute("onClick", "removeChildNodes('phone', '" + rmId + "')");
        rmlink.innerHTML = "－";

        // 親要素のラベルにspanとテキストボックスを追加
        label.append(rmlink);
        label.append(child);
        label.append(elm1Label)
        label.append(classId);

    } else if (obj === "birthday_list") {
        // 生年月日の場合
        // ダミーデータの生成
        var yy = Math.floor(Math.random() * (2020 - 1980)) + 1980;
        var mm = Math.floor(Math.random() * 11) + 1;
        var dd = Math.floor(Math.random() * 30) + 1;
        if (mm < 10) mm = "0" + mm;
        if (dd < 10) dd = "0" + dd;
        var dat = yy + "/" + mm + "/" + dd;
        var rSrc = [ "妻", "子", "友人", "恋人", "同僚", "上司", "部下", ];
        var r = rSrc[Math.floor(Math.random() * rSrc.length)];


        // 削除用に使うIDの生成
        const rmId = "birth" + (parseInt(parent.childElementCount) + 1);

        // 追加する要素の生成
        // ラベルのIDをセット
        label.setAttribute("id", rmId)
        
        // 生年月日の入力テキストボックス
        const child = document.createElement("input");
        child.setAttribute("value", dat);  // ダミーデータ
        child.setAttribute("type", "text");
        child.setAttribute("name", "BIRTHDAY[]");
        child.setAttribute("size", "10");

        // 「続柄：」を表示するspan要素
        elm1Label.innerHTML = " 　続柄：";

        // 続柄の入力テキストボックス
        const rchild = document.createElement("input");
        rchild.setAttribute("value", r);         // ダミーデータ
        rchild.setAttribute("type", "text");
        rchild.setAttribute("name", "RBIRTHDAY[]");
        rchild.setAttribute("size", "5");

        // 削除用リンク
        const rmlink = document.createElement("button");
        rmlink.setAttribute("class", "btn");
        rmlink.setAttribute("type", "button");
        rmlink.setAttribute("onClick", "removeChildNodes('birthday', '" + rmId + "');");
        rmlink.innerHTML = "－";

        // 親要素のラベルにspanとテキストボックスを追加
        label.append(rmlink);
        label.append(child);
        label.append(elm1Label)
        label.append(rchild);
    }
    // 親要素のdivにlabelを追加
    parent.append(label);
}