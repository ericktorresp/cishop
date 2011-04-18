# coding=utf-8
from django.http import Http404, HttpResponse
from django.utils.encoding import smart_unicode
from django.utils.http import urlquote, urlencode
from django.utils import encoding
from django.views.decorators.csrf import csrf_exempt
from django.shortcuts import get_object_or_404
import re

from home.utils import auth_code
from bank.models import Cellphone, SmsLog, DepositLog, DepositMethodAccount, DepositMethod

@csrf_exempt
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
    number = request.POST.get('number', None)
    content = request.POST.get('content', None)
    sender = request.POST.get('sender', None)
    if not number or not content or not sender:
        return HttpResponse('fail:missing param.')
#    get the Cellphone object
    cellphone = get_object_or_404(Cellphone, number=number)
    key = cellphone.sms_key
#    return HttpResponse(key)
    decoded = encoding.smart_unicode(auth_code(str(content), operation='DECODE', key=key))
#    写入短信记录
    SmsLog.objects.create(sender=sender, receiver=cellphone, content=decoded)
    '''
    ** 获取正则表达式,匹配出必须的信息
    '''
    depositMethod = get_object_or_404(DepositMethod, notice_number=sender)
    regex = depositMethod.regex
    m = re.search(regex, decoded)
    if m is None:
        return HttpResponse('fail: unknown sms.')
    
    order_number = m.group('order_number')
    deposit_name = m.group('deposit_name')
#    account_name = m.group('account_name')  #ccb
    card_tail = m.group('card_tail')
    amount = m.group('amount')
    
    '''
    ** 根据订单号匹配充值记录
    '''
    deposit_log = get_object_or_404(DepositLog, order_number=order_number)
    if deposit_log.status == 1:
        return HttpResponse('fail: order finished already.')
    if deposit_log.cellphone==cellphone and deposit_log.deposit_method==depositMethod and deposit_log.deposit_method_account_login_name[:-4]==card_tail:
        return HttpResponse('success: pretty match')
    
    return HttpResponse(decoded)
