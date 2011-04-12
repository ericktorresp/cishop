from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

from depositmethod import DepositMethod
from depositmethodaccount import DepositMethodAccount
from cellphone import Cellphone
from smslog import SmsLog

DEPOSITLOG_STATUS = (
    (0, _('progress')),
    (1, _('finished')),
)

class DepositLog(models.Model):
    order_number = models.CharField(_('order number'), max_length=15, unique=True)
    user = models.ForeignKey(User, verbose_name=_('user'))
    deposit_method = models.ForeignKey(DepositMethod, limit_choices_to={'enabled': True})
    deposit_method_account = models.ForeignKey(DepositMethodAccount, limit_choices_to={'enabled': True})
    deposit_method_account_login_name = models.CharField(_('receive account'), max_length=100, blank=True)
    deposit_method_account_account_name = models.CharField(_('receive account name'), max_length=50, blank=True)
    email = models.CharField(_('receive email'), max_length=100, blank=True, null=True)
    status = models.SmallIntegerField(_('status'), choices=DEPOSITLOG_STATUS, default=0)
    cellphone = models.ForeignKey(Cellphone, to_field="number", db_column="cellphone", limit_choices_to={'enabled': True})
    deposit_time = models.DateTimeField(_('deposit time'), auto_now_add=True)
    receive_log = models.ForeignKey(SmsLog, verbose_name=_('sms log'), null=True, blank=True)
    receive_time = models.DateTimeField(_('receive time'), null=True, blank=True, editable=False)
    
    def __unicode__(self):
        return 'order no. %s' % self.order_number
    
    def save(self, *args, **kwargs):
        self.deposit_method_account_login_name = self.deposit_method_account.login_name
        self.deposit_method_account_account_name = self.deposit_method_account.account_name
        self.email = self.deposit_method_account.email
        super(DepositLog, self).save(*args, **kwargs)
    
    class Meta:
        app_label = 'bank'
        db_table = u'deposit_log'
        verbose_name = _('deposit log')
        