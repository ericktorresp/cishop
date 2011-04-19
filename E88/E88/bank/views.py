# coding=utf-8
from django.http import Http404, HttpResponse
from django.utils.encoding import smart_unicode
from django.utils.http import urlquote, urlencode
from django.utils import encoding
from django.views.decorators.csrf import csrf_exempt
from django.shortcuts import get_object_or_404
from django.db import transaction
import re, datetime
from decimal import Decimal

from home.utils import auth_code
from bank.models import Cellphone, SmsLog, DepositLog, DepositMethodAccount, DepositMethod
from account.models import UserAccountDetail, UserProfile, UserAccountDetailType

@csrf_exempt
#@transaction.commit_manually
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
    number = request.POST.get('number', None)
    content = request.POST.get('content', None)
    sender = request.POST.get('sender', None)
    action_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    if not number or not content or not sender:
        return HttpResponse('fail:missing param.')
    cellphone = get_object_or_404(Cellphone, number=number)
    key = cellphone.sms_key
    decoded = encoding.smart_unicode(auth_code(str(content), operation='DECODE', key=key))
    receive_log = SmsLog.objects.create(sender=sender, receiver=cellphone, content=decoded)
#    sms_loging = transaction.savepoint()
    '''
    ** 获取正则表达式,匹配出必须的信息
    ** 1. 根据发送号获取充值方式
    ** 2. 读取该充值方式的正则表达式
    '''
    depositMethod = get_object_or_404(DepositMethod, notice_number=sender)
    regex = depositMethod.regex
    '''
    ** 3. 匹配正则，失败表示 sms 无效
    '''
    m = re.search(regex, decoded)
    if m is None:
        return HttpResponse('fail: unknown sms.')
    
    order_number = m.group('order_number')
    deposit_name = m.group('deposit_name')
    card_tail = m.group('card_tail')
    amount = Decimal(m.group('amount'))
    
    '''
    ** 根据订单号匹配充值记录, @todo: 如未找到相应的订单，直接写入异常充值表
    ** 工行匹配: 订单号, 手机号, 根据银行发送手机号获取的充值方式, 尾号, 
    ** 建行匹配: 订单号, 手机号, 根据银行发送手机号获取的充值方式, 尾号, 收款人
    ** 4. 判断订单状态, 已经结束(status=1)的直接返回
    '''
    deposit_log = get_object_or_404(DepositLog, order_number=order_number)
    if deposit_log.status == 1:
        return HttpResponse('fail: order finished already.')
    '''
    ** 5. 初步匹配手机号, 支付方式(工行,建行, etc.), 收款卡尾号
    '''
    if deposit_log.cellphone != cellphone:
        return HttpResponse('fail: cellphone did not match')
    elif deposit_log.deposit_method != depositMethod:
        return HttpResponse('fail: deposit method did not match')
    elif deposit_log.deposit_method_account_login_name[-4:] != card_tail:
        return HttpResponse('fail: card tail did not match')
    '''
    ** 6. 建行继续检查收款人
    '''
    if deposit_log.deposit_method.alias == 'ccb':
     account_name = m.group('account_name')
     if account_name != deposit_log.deposit_method_account_account_name:
         return HttpResponse('fail: account name did not match')
    '''
    ** 7. 更新充值记录为已处理
    '''
    deposit_log.status=1
    deposit_log.receive_log = receive_log
    deposit_log.receive_time = action_time
    deposit_log.save()
    '''
    ** 8. 给用户充值(user_profile, user_account_detail)
    '''
    user_profile = deposit_log.user.get_profile()
    '''
    ** 8.1 写帐变
    '''
    user_account_detail = UserAccountDetail.objects.create(
        from_user=deposit_log.user,
        detail_type=UserAccountDetailType.objects.get(pk__exact=36),
        description = 'user deposit',
        amount = amount,
        pre_balance = user_profile.available_balance,
        post_balance = user_profile.available_balance+amount,
        client_ip = '127.0.0.1',
        proxy_ip = '127.0.0.1',
        action_time = action_time
    )
    '''
    ** 8.2 更新用户可用金额
    '''
    user_profile.available_balance = user_profile.available_balance+amount
    user_profile.balance_update_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    user_profile.save()
    '''
    ** 如果在此过程中出现问题, 回滚到写入短信记录(无论正常与否, 短信记录必写)
    '''
#    transaction.savepoint_commit(sms_loging)
#    transaction.commit()
    return HttpResponse('success')
