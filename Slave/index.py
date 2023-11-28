from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import http.server
import socketserver
from urllib.parse import urlparse, parse_qs
import json
import requests
global_params = {}  # 存储参数的全局变量
def check(require_content,uid,platform):
    def check_sensitive_words(sensitive_file, require_content):
        with open(sensitive_file, 'r', encoding='utf-8') as f:
            sensitive_words = [word.strip() for word in f.readlines()]

        found_sensitive = [word for word in sensitive_words if word in require_content]

        if found_sensitive:
            print(f"发现敏感词! : {found_sensitive}")
            url = "=服务器地址/recall.php?uid="+uid+"&platform="+platform+"&passed=0"
            try:
                response = requests.get(url)
                response.raise_for_status()
            except requests.exceptions.RequestException as e:
                print("回调请求失败:", e)
        else:
            print("未发现敏感词")
            url = "https://api.yuxiangwang0525.com/scc/recall.php?uid="+uid+"&platform="+platform+"&passed=1"
            try:
                response = requests.get(url)
                response.raise_for_status()
            except requests.exceptions.RequestException as e:
                print("回调请求失败:", e)

    sensitive_file = 'sensitive.txt'
    check_sensitive_words(sensitive_file, require_content)
def requestbili(uid):
    url = "https://api.vc.bilibili.com/dynamic_svr/v1/dynamic_svr/space_history?host_uid="+uid
    response = requests.get(url)
    if response.status_code == 200:
        data = response.json()
        data = json.dumps(data,ensure_ascii=False)
        # 处理返回的数据
        #print(data)
        return data
    else:
        print("请求失败，状态码：", response.status_code)
def catchmys(uid):
    # 设置Chrome Driver路径
    chrome_driver_path = "./chromedriver.exe"

    # 创建Chrome Driver服务
    service = Service(chrome_driver_path)

    # 指定Chrome浏览器路径及版本
    chrome_binary_path = "./Chrome-bin/chrome.exe"
    options = webdriver.ChromeOptions()
    options.binary_location = chrome_binary_path

    # 启动Chrome浏览器
    driver = webdriver.Chrome(service=service, options=options)

    # 导航到目标页面
    driver.get("https://www.miyoushe.com/ys/accountCenter/replyList?id="+uid)

    # 等待页面加载完毕
    wait = WebDriverWait(driver, 10)

    # 等待mhy-container mhy-account-center-header元素出现
    element = wait.until(EC.presence_of_element_located((By.CLASS_NAME, "mhy-container")))

    # 抓取页面内容存储到变量
    page_content = driver.page_source

    # 输出页面内容
    #print(page_content)

    # 关闭浏览器
    driver.quit()
    return page_content;
class MyHttpRequestHandler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        parsed_path = urlparse(self.path)
        if parsed_path.path == '/checker':
            params = parse_qs(parsed_path.query)
            platform = params.get('platform', [''])[0]
            uid = params.get('uid', [''])[0]
            global global_params
            global_params['platform'] = platform
            global_params['uid'] = uid
            
            response_data = {}
            if platform == "bilibili" or platform == "miyoushe":
                response_data['code'] = 0
                response_data['msg'] = 'success'
            else:
                response_data['code'] = 40
                response_data['msg'] = 'platform error'
            self.send_response(200)
            self.send_header("Content-type", "application/json")
            self.end_headers()
            self.wfile.write(bytes(json.dumps(response_data), 'utf-8'))
            if(platform=="miyoushe"):
                check(catchmys(uid),uid,platform)
            elif(platform=="bilibili"):
                check(requestbili(uid),uid,platform)
        else:
            self.send_response(404)
            self.end_headers()
            self.wfile.write(bytes('Not Found', 'utf-8'))

port = 15025
handler_object = MyHttpRequestHandler
my_server = socketserver.TCPServer(("0.0.0.0", port), handler_object)

try:
    print('正在监听', port,"端口")
    my_server.serve_forever()
except KeyboardInterrupt:
    my_server.shutdown()
    

