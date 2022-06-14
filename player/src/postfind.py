import requests,sys,time,lxml
from bs4 import BeautifulSoup
from urllib import parse

try:

    headers = { 'User-Agent': 'Mozilla/5.0 (Linux; Android 8.0; Pixel 2 Build/OPD3.170816.012) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Mobile Safari/537.36' }

    url = 'https://blog.naver.com/PostSearchList.naver?blogId=12si27&categoryNo=0&range=all&SearchText=' + parse.quote(sys.argv[1])
    r = requests.get(url, headers = headers).text
    bs = BeautifulSoup(r, 'lxml')

    # 검색결과 조회
    result = bs.find('tr', {'valign' : 'top'})

    # 첫번째 결과 추출
    res_href = result.find('a')['href']

    # 만약 링크에 'section'이 있다 -> 검색결과 없는것이므로 NOTFOUND 리턴
    if res_href.find('section') != -1:
        print('<result>NOTFOUND</result>')
    else:
        print('<result>' + res_href + '</result>')
        
except:

    print('<result>ERROR</result>')