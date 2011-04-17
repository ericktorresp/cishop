# coding=utf-8
from django.http import Http404, HttpResponse
from django.utils.encoding import smart_unicode
from django.utils.http import urlquote, urlencode
from django.utils import encoding
import re

from home.utils import auth_code
from bank.models import Cellphone, SmsLog, DepositLog, DepositMethodAccount

def receive(request):
    '''
    ** the encoding on os x is utf8, on windows is gb2312(except safari)
    ** 王大有已于4月4日向尾号为4112的工行账户汇入10元。<王大有留言：110411102759888>。【工商银行】
    ** 尊敬的岳志国客户：您好，赵朋丽已成功向您尾号为6866的账号转入人民币5.00元，请注意查收。 留言为:110415144155143[建设银行]。
    ** 亲爱的用户：王大有通过支付宝向您(caicai1205@vip.sina.com)付款244元。
    ** 根据  sender 获取充值方式详细信息，包括正则模板，用正则模板匹配短信内容
    ** number=13000000000&content=加密后字符串（包含sender=95588）
    ** sms 端使用 urllib post 到服务端，读取结果（success|fail[fail messages]）
    '''
#    return HttpResponse(u'：'.__repr__())
#    request.encoding = 'utf8'
#    request.encoding = 'gb2312'
#    string = request.GET.get('content', '')
    number = request.GET.get('number', None)
    if number is None:
        return HttpResponse()
#    get the Cellphone object
    cellphone = Cellphone.objects.get(number__exact=number)
    
    key='NcElTeV1W5g7KCx3BMSIp2htNE9sjk1R'
    decoded = encoding.smart_unicode(auth_code(str(request.GET.get('content', '')), operation='DECODE', key=key))
#    return HttpResponse(decoded.__repr__())
    m = re.search('^\D{3}(?P<account_name>\D+)\D{2}'+u'\uff1a'+'\D{3}(?P<deposit_name>\D+)\D{8}(?P<card_tail>\d{4})\D{8}(?P<amount>\S+)\D{12}\:(?P<order_number>\d+)\[\D+\]\D+$', decoded)
    if m:
        return HttpResponse(m.group('amount'))
    
    return HttpResponse(decoded)

def match_chinese(s, f, i):
    global fd_output
    r = re.compile('\"[^\"]*[\x80-\xff]{3}[^\"]*\"')
    s_match = r.findall(s)
    for c in s_match:
        str = "%s ( %d ): %s\n" % (f, i, c)
        fd_output.write(str)
