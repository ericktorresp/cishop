# coding=utf-8
from django.http import Http404, HttpResponse
from django.utils.encoding import smart_unicode
from django.utils.http import urlquote, urlencode
from django.utils import encoding
import re

def receive(request):
    '''
    ** the encoding on os x is utf8, on windows is gb2312(except safari)
    ** 王大有已于4月4日向尾号为4112的工行账户汇入10元。<王大有留言：110411102759888>。【工商银行】
    ** 亲爱的用户：王大有通过支付宝向您(caicai1205@vip.sina.com)付款244元。
    ** 根据  sender 获取充值方式详细信息，包括正则模板，用正则模板匹配短信内容
    '''
#    request.encoding = 'utf8'
    request.encoding = 'gb2312'
    string = request.GET.get('content', '')
    m = re.search('(?P<deposit_name>\D+)\D{2}\d{1,2}\D{1}\d{1,2}\D{5}(?P<card_tail>\d{4})\D{7}(?P<amount>.*)\D{2}<\D+(?P<order_number>\d*)>\S+', string)  # ICBC
    if m:
        return HttpResponse(m.group('order_number'))
    
    return HttpResponse(request.GET.get('content', ''))

def match_chinese(s, f, i):
    global fd_output
    r = re.compile('\"[^\"]*[\x80-\xff]{3}[^\"]*\"')
    s_match = r.findall(s)
    for c in s_match:
        str = "%s ( %d ): %s\n" % (f, i, c)
        fd_output.write(str)
