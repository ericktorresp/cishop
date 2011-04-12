# coding=utf-8
from django.http import Http404, HttpResponse
from django.utils.encoding import smart_unicode
from django.utils.http import urlquote, urlencode
from django.utils import encoding
import re

def receive(request):
    '''
    the encoding on os x is utf8, on windows is gb2312(except safari)
    赵振波已于4月4日向尾号为4112的工行账户汇入10元。<赵振波留言：201104042123123>。【工商银行】
    '''
    request.encoding = 'utf8'
    string = request.GET.get('content', '')
    m = re.search('(?P<deposit_name>\D+)\D{2}\d\S+(?P<card_tail>\d{4})\D+(?P<amount>\d+)\S+<\D+(?P<order_number>.*)>\S+', string)  # order number
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
