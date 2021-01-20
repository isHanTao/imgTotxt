<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="图片,文字识别,文字提取,在线识别,中文,图片识别">
    <meta name="description" content="免费图片文字识别，不会保留使用者任何信息，安全可靠">
    <title>图片文字在线提取</title>

    <!-- Fonts -->
<!--    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">-->

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }
        *{
            padding: 0;
            margin: 0;
            outline: unset;
        }
        .upload{
            height: 200px;
            width: 300px;
            text-align: center;
            justify-content: center;
            display: flex;
            align-items: center;
            border: #c4c4c4 dashed 1px;
            border-radius: 5px;
        }
        .image{
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        #preview{
            height: 500px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e0e0e0;
        }
        #preview > image{
            max-width: 100%;
        }
    </style>
</head>
<body style="position: relative;width: 80%;margin: auto">
<div class="image">
    <div class="upload">粘贴解析图片</div>
</div>
<p>图片预览</p>
<div id="preview"></div>
<p id="log"></p>
<div id="out">
</div>
</body>
<script src="/js/jquery.js"></script>
<script type="text/javascript">
    function dataURItoBlob(dataURI) {
        var byteString = atob(dataURI.split(',')[1]);
        var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
        var ab = new ArrayBuffer(byteString.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        return new Blob([ab], {type: mimeString});
    }
    document.addEventListener('paste', function (event) {
        var items = (event.clipboardData || window.clipboardData).items;
        var file = null;
        if (items && items.length) {
            // 搜索剪切板items
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    file = items[i].getAsFile();
                    break;
                }
            }
        } else {
            log.innerHTML = '<span style="color:red;">当前浏览器不支持</span>';
            return;
        }
        if (!file) {
            log.innerHTML = '<span style="color:red;">粘贴内容非图片</span>';
            return;
        }
        var formData = new FormData();
        var reader = new FileReader()
        reader.onload = function (event) {
            $('#preview').empty().append('<img src="' + event.target.result + '" class="upload-image">');
            formData.append('file',dataURItoBlob(event.target.result))
            $.ajax({
                url: '/api/upload',
                type: 'post',
                contentType: false,
                cache: false,
                accept:'application/json',
                processData: false,
                data: formData,
                success: function (res) {
                    res = JSON.parse(res)
                    console.log(res)
                    if(res.code === 1){
                        log.innerHTML = '<span style="color:red;">图片解析失败</span>';
                    }else{
                        let html = '';
                        for (let i = 0; i < res.data.words_result.length; i++) {
                            html += res.data.words_result[i].words
                        }
                        $('#out').empty().append(html)
                    }
                },
            })
        }
        reader.readAsDataURL(file);

    })
</script>
</html>
