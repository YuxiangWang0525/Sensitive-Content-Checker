maketable();
let count = 1;
function remaketable(){
    document.getElementById('Table').innerHTML = "";
    count = 1;
    maketable();
}
function maketable(){
    fetch('服务器地址/scc/admin/list.php')
    .then(response => response.json())
    .then(data => {
        const table = document.getElementById('Table');
        
        data.forEach(item => {
        let platformText, statusText, statusColor;
        
        if (item.platform === 'bilibili') {
            platformText = '哔哩哔哩';
        } else if (item.platform === 'miyoushe') {
            platformText = '米游社';
        } else {
            platformText = '';
        }

        if (item.status === 'nopass') {
            statusText = '审核失败';
            statusColor = 'red';
        } else if (item.status === 'passed') {
            statusText = '审核通过';
            statusColor = 'green';
        } else if (item.status === 'doing') {
            statusText = '审核中';
            statusColor = 'black';
        } else if (item.status === 'waiting') {
            statusText = '待审';
            statusColor = 'darkorange';
        } else {
            statusText = '';
            statusColor = '';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${count}</td>
            <td id="${count}_uid">${item.uid}</td>
            <td id="${count}_platform">${platformText}</td>
            <td style="color:${statusColor}">${statusText}</td>
            <td>
            <a type="button" class="btn btn-block btn-${getButtonColor(item.status)} btn-sm${isButtonDisabled(item.status)}" id="${count}_button" href="javascript:retry(${count});">${getButtonText(item.status)}</a>
            </td>
        `;
        
        table.appendChild(row);
        count++;
        });
    });

    function getButtonColor(status) {
    if (status === 'nopass') {
        return 'danger';
    } else if (status === 'passed') {
        return 'success disabled';
    } else if (status === 'doing') {
        return 'primary disabled';
    } else if (status === 'waiting') {
        return 'primary';
    } else {
        return '';
    }
    }

    function isButtonDisabled(status) {
    if (status === 'doing') {
        return ' disabled';
    } else {
        return '';
    }
    }

    function getButtonText(status) {
    if (status === 'nopass') {
        return '重新提交审核';
    } else if (status === 'passed') {
        return '审核通过,无需操作';
    } else if (status === 'doing') {
        return '正在审核,无法操作';
    } else if (status === 'waiting') {
        return '立即提交任务';
    } else {
        return '';
    }
    }
}
function retry(electid){
    var retryuid = document.getElementById(electid+'_uid');
    var showretryplatform = document.getElementById(electid+'_platform').innerText;
    var retrybutton = document.getElementById(electid+'_button');
    var retryplatform;
    switch(showretryplatform){
        case '哔哩哔哩':
            retryplatform = "bilibili";
            break;
        case '米游社':
            retryplatform = "miyoushe";
            break;
    }
    // 创建XMLHttpRequest对象
    var xhr = new XMLHttpRequest();
    // 设置POST请求的URL和参数
    var url = '服务器地址/scc/do.php';
    var params = 'uid=' + encodeURIComponent(retryuid.innerText) + '&platform=' + encodeURIComponent(retryplatform);

    // 初始化请求
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // 发送请求
    xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
        if (xhr.status === 200) {
        // 请求成功，获取返回的JSON数据
        var response = JSON.parse(xhr.responseText);
        
        // 判断返回值是否为0
        if (response.code === 0) {
            retrybutton.className = "btn btn-block btn-primary btn-sm disabled";
            retrybutton.innerText = "正在重新提交审核";
            setTimeout("remaketable()", 5000);
        } else {
            alert(response.msg); // 弹出msg字段的内容
        }
        } else {
        console.error('请求失败');
        }
    }
    };

    xhr.send(params);

}